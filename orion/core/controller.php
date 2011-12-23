<?php

namespace Orion\Core;


/**
 * \Orion\Core\Controller
 * 
 * Orion controller base class.
 * 
 * Extend this class to create a new controller.
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 *
 * @author Thibaut Despoulain
 * @version 0.11.12
 */
abstract class Controller
{
    /**
     * Security prefix for controller methods.
     * Each module function name must start with this prefix.
     */
    const FUNCTION_PREFIX = '_';
    
    /**
     * Module name placeholder, used for base routing.
     * Module name must be lowercase.
     * If module name is "module" then you can access it via BASEURL/module(.html|/uri.html).
     * @var string
     */
    protected $name = null;

    /**
     * Module route object placeholder.
     * Must be created in child module consturctor.
     *
     * @var OrionRoute
     */
    protected $route = null;

    /**
     * Resticted functions names
     */
    private $RESTRICTED_FUNCTIONS = array('__construct'
                                      ,'__destruct'
                                      ,'toString'
                                      ,'load'
                                      ,'isRestrictedFunction'
                                      ,'respond');
    
    private $FUNCTION_NAME_MATCH = '[a-zA-Z_]+';

    /**
     * Main module function, executed right after module loading by Orion.
     * Handles route parsing and function callbacks.
     */
    public function load()
    {
        if ( $this->route == null )
        {
            if ( !\Orion::config()->defined( 'ROUTING_AUTO' ) || \Orion::config()->get( 'ROUTING_AUTO' ) == false )
                throw new Exception( 'No route object found in controller and automatic routing is disabled.', E_USER_ERROR, get_class( $this ) );

            $this->route = new Route();
            $function = $this->route->decodeAuto();
        }
        else
        {
            $function = $this->route->decode();
        }

        if ( Tools::startWith( $function->getName(), '__' ) )
            throw new Exception( 'Trying to access a resticted function, you are not allowed to use methods starting with "__".', E_USER_ERROR, get_class( $this ) );

        if ( Tools::startWith( $function->getName(), self::FUNCTION_PREFIX ) )
            throw new Exception( 'Function name in rule must be declared without function prefix ' . self::FUNCTION_PREFIX . '.', E_USER_ERROR, get_class( $this ) );

        if ( !is_callable( array( $this, self::FUNCTION_PREFIX . $function->getName() ) ) )
            Context::redirect( 404 );

        Tools::callClassMethod( $this, self::FUNCTION_PREFIX . $function->getName(), $function->getArgs() );
    }

    /**
     * Allows access only to logged users that have a level equal to or less than provided role. If permission is not granted, it will automatically redirect the user to the login module.
     * <p><b>Note that while it's doing all login/auth/redirection work automatically, you still have to create the corresponding user table in your database in addition to provide the login module into orion's module directory.</b></p>
     * @see Auth
     *      MainConfig
     *      LoginModule
     * @param string $slug the role identifier (ie: 'administrator', 'member', etc.). See your configuration file for a liste of roles and their permission level.
     */
    public function allow( $slug )
    {
        Auth::login();
        if ( !Auth::allow( $slug ) )
        {// this exception prevents any redirection defect or hack
            throw new Exception( 'Access denied', E_USER_ERROR, $this->name );
        }
    }

    /**
     * Write response to output
     * @param mixed $output
     * @param boolean $exit 
     * @param int $code the status code to use
     */
    public function respond( $output, $exit=true, $code=null )
    {
        if ( $code != null )
            Context::setHeaderCode( $code );
        echo $output;
        if ( $exit )
            exit();
    }

    /**
     * Security function name testing. (Not used as of now)
     *
     * @param string Function name to test
     * @deprecated
     * @see OrionSecurity
     */
    private function isRestrictedFunction( $name )
    {
        return (!Tools::startWith( $function, '_' )
                && Tools::match( $function, $this->FUNCTION_NAME_MATCH )
                && in_array( $function, $this->RESTRICTED_FUNCTIONS ));
    }

    /**
     * Returns module name identifier
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}

?>
