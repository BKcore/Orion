<?php

/**
 * Orion authentification class.
 *
 * <p>No cookie session remembering feature provided.
 * I just don't like keeping sessions or password localy.</p>
 *
 * <p><b>For performance matters, roles and levels are stored into an arrayMap
 * in configuration file</b></p>
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 *
 * @static
 */

namespace Orion\Core;

use \Orion\Models;

class Auth
{
    /**
     * Core\Auth error : Login field mismatch
     */
    const E_LOGIN_MISMATCH = 50;
    /**
     * Core\Auth error : Password field mismatch
     */
    const E_PASSWORD_MISMATCH = 51;
    /**
     * Core\Auth error : Permission level too low
     */
    const E_LEVEL_RESTRICT = 52;
    /**
     * Core\Auth error : Missing login data
     */
    const E_NO_DATA = 53;
    /**
     * Core\Auth error Unknown error
     */
    const E_UNKNOWN = 54;
    /**
     * Core\Auth error : User is not verified
     */
    const E_NOT_VERIFIED = 55;

    /**
     * User data (once logged in)
     * @var Core\AuthUser $user
     */
    private static $user = null;

    /**
     * Try loging the user in.
     * <ul>
     * <li>Checks if already logged in session var</li>
     * <li>If not, check for post data from login module</li>
     * <li>Otherwise, redirect to login module</li>
     * </ul>
     * <p>If user if found and password/login match, user data is stored into
     * $this->user attributes, that you can access using Core\Auth->user()->name;
     * for example.</p>
     * <p>If login fails, a redirection occurs toward login module
     * , using /error/err_code routing.</p>
     */
    public static function login( $noredirect=false )
    {
        if ( isset( $_SESSION[ 'orionauth' ] ) )
        {
            $session = new Models\Auth\User();
            $session->fromArray( $_SESSION[ 'orionauth' ] );
            if ( \Orion::isDebug() )
                var_dump( $session );
            self::$user = $session;
            return true;
        }
        else
        {
            if ( isset( $_POST[ 'auth' ] ) && isset( $_POST[ 'login' ] ) && isset( $_POST[ 'password' ] ) )
            {
                $data = Models\Auth\User::get()
                        ->where( 'login', Query::EQUAL, $_POST[ 'login' ] )
                        ->limit( 1 )
                        ->fetch();
                if ( $data != false )
                {
                    if ( Models\Auth\User::hasField( 'verified' ) && $data->verified == 0 )
                    {
                        if ( $noredirect )
                            return false;
                        else
                            Context::redirect( Context::genModuleURL( \Orion::config()->get('AUTH_MODULE'), 'error-' . self::E_NOT_VERIFIED ), 'default' );
                    }
                    $hash = Security::saltedHash( $_POST[ 'password' ], $_POST[ 'login' ] );
                    if ( $hash == $data->password )
                    {
                        $session = new Models\Auth\User();
                        $session->login = $data->login;
                        $session->level = $data->level;
                        $session->name = $data->name;
                        $session->id = $data->id;

                        self::$user = $session;
                        $_SESSION[ 'orionauth' ] = $session->toArray();
                        return true;
                    }
                    else
                    {
                        if($noredirect) return false;
                        else Context::redirect(Context::genModuleURL(\Orion::config()->get('AUTH_MODULE'), 'error-'.self::E_PASSWORD_MISMATCH), 'default');
                    }
                }
                else
                {
                    if($noredirect) return false;
                        else Context::redirect(Context::genModuleURL(\Orion::config()->get('AUTH_MODULE'), 'error-'.self::E_LOGIN_MISMATCH), 'default');
                }
            }
            else
            {
                $_SESSION['orion_auth_target'] = Context::getModuleURL();
                if($noredirect) return false;
                else Context::redirect(Context::genModuleURL(\Orion::config()->get('AUTH_MODULE'), 'login', 'default'));
            }
        }
    }

    /**
     * Manual login method
     * @param type $user
     * @param type $password
     * @return int Returns 0 if success, else returns a specific error code that is > 0
     */
    public static function manualLogin( $user, $password )
    {
        try
        {
            if ( empty( $user ) || empty( $password ) )
                return self::E_NO_DATA;

            $data = Models\Auth\User::get()
                                   ->where( 'login', Query::EQUAL, $user )
                                   ->limit( 1 )
                                   ->fetch();

            if ( $data != false )
            {
                if ( Models\Auth\User::hasField( 'verified' ) && $data->verified == 0 )
                {
                    return self::E_NOT_VERIFIED;
                }
                $hash = Security::saltedHash( $password, $user );
                if ( $hash == $data->password )
                {
                    $session = new Models\Auth\User();
                    $session->login = $data->login;
                    $session->level = $data->level;
                    $session->name = $data->name;
                    $session->id = $data->id;

                    self::$user = $session;
                    $_SESSION[ 'orionauth' ] = $session->toArray();
                    return 0;
                }
                else
                {
                    return self::E_PASSWORD_MISMATCH;
                }
            }
            else
            {
                return self::E_LOGIN_MISMATCH;
            }
        }
        catch ( Exception $e )
        {
            throw $e;
        }
    }

    /**
     * <p><b>Must be called AFTER Core\Auth::login()</b></p>
     * Allows access only to logged users that have a level equal to or less than provided role. If permission is nsot granted, it will automatically redirect the user to the login module.
     * <p><b>Note that while it's doing all login/auth/redirection work automatically, you still have to create the corresponding user table in your database in addition to provide the login module into orion's module directory.</b></p>
     * @see Core\Auth
     *      MainConfig
     *      LoginModule
     * @param string $slug the role identifier (ie: 'administrator', 'member', etc.). See your configuration file for a liste of roles and their permission level.
     * @return bool TRUE if user has the permission, FALSE otherwise (even if redirected)
     */
    public static function allow( $slug, $noredirect=false )
    {
        if ( !self::logged() )
            self::login();

        $roles = \Orion::config()->get( 'AUTH_ROLES' );

        if ( !array_key_exists( $slug, $roles ) )
            throw new Exception( 'Unable to restrict access, role [' . $slug . '] does not exist.', E_USER_ERROR, __CLASS__ );

        if ( self::$user == null || empty( self::$user->level ) || self::$user->level <= 0 )
            throw new Exception( 'Missing user information. See Core\Auth for more info.', E_USER_ERROR, __CLASS__ );

        if ( self::$user->level > $roles[ $slug ] )
        {
            Context::setHeaderCode( 403 );
            if ( !$noredirect )
                Context::redirect( Context::genModuleURL( 'users', 'error-' . self::E_LEVEL_RESTRICT, 'admin' ) );
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * Logs the user out.
     * Unsets the session data.
     */
    public static function logout()
    {
        // Unset all of the session variables.
        $_SESSION = array( );

        // Delete the session cookie.
        if ( isset( $_COOKIE[ session_name() ] ) )
            setcookie( session_name(), '', time() - 42000, '/' );

        // Finally, destroy the session itself.
        session_destroy();

        self::$user = null;
    }

    /**
     * Is a user logged in ?
     * <p><b>Not for security testing !!! Use allow($slug) instead</b></p>
     * @return bool
     */
    public static function logged()
    {
        if ( isset( $_SESSION[ 'orionauth' ] ) )
        {
            if ( empty( self::$user ) )
                return self::login( true );
            else
                return true;
        }

        return false;
    }

    /**
     * Gets user data
     * @return Core\AuthUser $user
     */
    public static function user()
    {
        return self::$user;
    }

}

?>
