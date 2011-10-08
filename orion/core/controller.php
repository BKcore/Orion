<?php
/**
 * Orion controller base class.
 * 
 * Extend this class to create a new controller.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
namespace Orion\Core;

abstract class Controller
{
    const FUNCTION_PREFIX = '_';
    
    /**
     * Module name placeholder, used for base routing.
     * Module name must be lowercase.
     * If module name is "module" then you can access it via BASEURL/module/.
     * @example A GuestbookModule module name has to be guestbook
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
                                      ,'setView');
    
    private $FUNCTION_NAME_MATCH = '[a-zA-Z_]+';

    /**
     * Main module function, executed right after module loading by Orion.
     * Handles route parsing and function callbacks.
     */
    public function load()
    {
		if($this->route == null)
		{
            if(!\Orion::config()->defined('ROUTING_AUTO') || \Orion::config()->get('ROUTING_AUTO') == false)
				throw new Exception('No route object found in module and automatic routing is disabled.', E_USER_ERROR, get_class($this));
				
			$this->route = new Route();
			$function = $this->route->decodeAuto();
		}
		else
		{
		    $function = $this->route->decode();
		}

        if(Tools::startWith($function->getName(), '__'))
            throw new Exception('Trying to access a resticted function.', E_USER_ERROR, get_class($this));

        if(Tools::startWith($function->getName(), self::FUNCTION_PREFIX))
            throw new Exception('Function name in rule must be declared without function prefix '.self::FUNCTION_PREFIX.'.', E_USER_ERROR, get_class($this));

        if(!is_callable(array($this, self::FUNCTION_PREFIX.$function->getName())))
            Context::redirect(404);

        Tools::callClassMethod($this, self::FUNCTION_PREFIX.$function->getName(), $function->getArgs());
    }

    /**
     * Allows access only to logged users that have a level equal to or less than provided role. If permission is not granted, it will automatically redirect the user to the login module.
     * <p><b>Note that while it's doing all login/auth/redirection work automatically, you still have to create the corresponding user table in your database in addition to provide the login module into orion's module directory.</b></p>
     * @see Auth
     *      MainConfig
     *      LoginModule
     * @param string $slug the role identifier (ie: 'administrator', 'member', etc.). See your configuration file for a liste of roles and their permission level.
     */
    public function allow($slug)
    {
        Auth::login();
        if(!Auth::allow($slug))
        {// this exception prevents any redirection defect or hack
            throw new Exception('Access denied', E_USER_ERROR, $this->name);
        }
    }
    
    /**
     * Write response to output
     * @param mixed $output
     * @param boolean $exit 
	 * @param int $code the status code to use
     */
    public function respond($output, $exit=true, $code=null)
    {
        if($code != null)
            Context::setHeaderCode($code);
        echo $output;
        if($exit) exit();
    }

    /**
     * Security function name testing.
     *
     * @param string Function name to test
     *
     * @see OrionSecurity
     */
    private function isRestrictedFunction($name)
    {
        return (!Tools::startWith($function, '_')
                && Tools::match($function, $this->FUNCTION_NAME_MATCH)
                && in_array($function, $this->RESTRICTED_FUNCTIONS));
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
