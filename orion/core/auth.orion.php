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
 */
class OrionAuth
{
    const CLASS_NAME = 'OrionAuth';
    /**
     * Auth error : Login field mismatch
     */
    const E_LOGIN_MISMATCH = 10;
    /**
     * Auth error : Password field mismatch
     */
    const E_PASSWORD_MISMATCH = 11;
    /**
     * Auth error : Permission level too low
     */
    const E_LEVEL_RESTRICT = 12;

    /**
     * User data (once logged in)
     * @var stdClass $user
     */
    private static $user=null;

    /**
     * Try loging the user in.
     * <ul>
     * <li>Checks if already logged in session var</li>
     * <li>If not, check for post data from login module</li>
     * <li>Otherwise, redirect to login module</li>
     * </ul>
     * <p>If user if found and password/login match, user data is stored into
     * $this->user attributes, that you can access using OrionAuth->user()->name;
     * for example.</p>
     * <p>If login fails, a redirection occurs toward login module
     * , using /error/err_code routing.</p>
     */
    public static function login()
    {
        if(isset($_SESSION['orionauth']))
            self::$user = $_SESSION['orionauth'];
        else
        {
            if(isset($_POST['auth']) && isset($_POST['login']) && isset($_POST['password']))
            {
                $auth = new OrionAuthUserHandler();
                $data = $auth->select()
                             ->where('login', '=', $_POST['login'])
                             ->limit(1)
                             ->fetch();
                if($data != false)
                {
                    $hash = OrionSecurity::saltedHash($_POST['password'], $_POST['login']);
                    if($hash == $data->password)
                    {
                        $session = new stdClass();
                        $session->login = $data->login;
                        $session->level = $data->level;
                        $session->name = $data->name;

                        self::$user = $session;
                        $_SESSION['orionauth'] = $session;
                    }
                    else
                    {
                        OrionContext::redirect(OrionContext::genModuleURL('login', '/error/'.self::E_PASSWORD_MISMATCH), 'default');
                    }
                }
                else
                {
                    OrionContext::redirect(OrionContext::genModuleURL('login', '/error/'.self::E_LOGIN_MISMATCH), 'default');
                }
            }
            else
            {
                $_SESSION['orion_auth_target'] = OrionContext::getModuleURL();
                OrionContext::redirect(OrionContext::genModuleURL('login', '', 'default'));
            }
        }
    }

    /**
     * <p><b>Must be called AFTER OrionAuth::login()</b></p>
     * Allows access only to logged users that have a level equal to or less than provided role. If permission is not granted, it will automatically redirect the user to the login module.
     * <p><b>Note that while it's doing all login/auth/redirection work automatically, you still have to create the corresponding user table in your database in addition to provide the login module into orion's module directory.</b></p>
     * @see OrionAuth
     *      MainConfig
     *      LoginModule
     * @link http://bkcore.com/labs.o/post/orion/How_to_set_up_user_auth
     * @param string $slug the role identifier (ie: 'administrator', 'member', etc.). See your configuration file for a liste of roles and their permission level.
     * @return bool TRUE if user has the permission, FALSE otherwise (even if redirected)
     */
    public static function allow($slug)
    {
        $roles = Orion::config()->get('AUTH_ROLES');
        
        if(!array_key_exists($slug, $roles))
            throw new OrionException('Unable to restrict access, role ['.$slug.'] does not exist.', E_USER_ERROR, self::CLASS_NAME);
    
        if(self::$user == null)
            throw new OrionException('You need to call OrionAuth::login() before OrionAuth::allow()', E_USER_ERROR, self::CLASS_NAME);

        if(self::$user->level > $roles[$slug])
        {
            OrionContext::redirect(OrionContext::genModuleUrl('login', '/error/'.self::E_LEVEL_RESTRICT), 'default');
            return false;
        }
        else
        {
            return true;
        }
    }

    public static function logout()
    {
        unset($_SESSION['orionauth']);
        self::$user = null;
    }

    /**
     * Is a user logged in ?
     * <p><b>Not for security testing !!! Use allow($slug) instead</b></p>
     * @return bool
     */
    public static function logged()
    {
        return (isset($_SESSION['orionauth']));
    }

    /**
     * Gets user data
     * @return stdClass $user
     */
    public static function user()
    {
        return self::$user;
    }
}

/**
 * Orion authentification sub user class object.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class OrionAuthUser
{
    public $id;
    public $name;
    public $login;
    public $password;
    public $level;
}

/**
 * Orion authentification sub user model handler.
 *
 * <p><b>Remember to create the corresponding table in database !</b></p>
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class OrionAuthUserHandler extends OrionModel
{
    protected $CLASS_NAME = 'OrionAuthUserHandler';

    public function bindAll()
    {
        $this->bindTable(Orion::config()->get('AUTH_TABLE_USER'));
        $this->bindClass('OrionAuthUser');
        $this->bind('id', $this->PARAM_ID(), 'Identifier', true);
        $this->bind('name', $this->PARAM_STR(255), 'Name');
        $this->bind('login', $this->PARAM_STR(100), 'Login');
        $this->bind('password', $this->PARAM_STR(1000), 'Password');
        $this->bind('level', $this->PARAM_INT(), 'Level');
    }
}
?>
