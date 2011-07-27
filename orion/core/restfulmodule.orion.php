<?php
/**
 * Orion RESTful module base class.
 * Extend this class to create a new REST module.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
abstract class OrionRestfulmodule
{
    const CLASS_NAME = 'OrionRestfulmodule';
    const FUNCTION_PREFIX = '_';
    
    const E_LOGIN_ERROR = 2;
    const E_LOGIN_DISALLOW = 4;
    const E_ROUTE_NO = 8;
    const E_FUNCTION_NO = 16;
    
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
            $this->sendError(self::E_ROUTE_NO);

        $function = $this->route->decode();

        if(OrionTools::startWith($function->getName(), '__'))
            $this->sendError(self::E_FUNCTION_NO);

        if(OrionTools::startWith($function->getName(), self::FUNCTION_PREFIX))
            $this->sendError(self::E_FUNCTION_NO);

        if(!is_callable(array($this, self::FUNCTION_PREFIX.$function->getName())))
            $this->sendError(self::E_FUNCTION_NO);

        OrionTools::callClassMethod($this, self::FUNCTION_PREFIX.$function->getName(), $function->getArgs());
    }

    /**
     * Allows access only to logged users that have a level equal to or less than provided role. If permission is not granted, it will automatically redirect the user to the login module.
     * <p><b>Note that while it's doing all login/auth/redirection work automatically, you still have to create the corresponding user table in your database in addition to provide the login module into orion's module directory.</b></p>
     * @see OrionAuth
     *      MainConfig
     *      LoginModule
     * @link http://bkcore.com/labs.o/post/orion/How_to_set_up_user_auth
     * @param string $slug the role identifier (ie: 'administrator', 'member', etc.). See your configuration file for a liste of roles and their permission level.
     */
    public function allow($slug)
    {
        try {
            if(!OrionAuth::login(true))
            {
                $this->sendError(self::E_LOGIN_ERROR);
            }
            if(!OrionAuth::allow($slug))
            {// this exception prevents any redirection defect or hack
                $this->sendError(self::E_LOGIN_DISALLOW);
            }
        }
        catch(OrionException $e)
        {
            throw $e;
        }
    }
    
    public function send($array)
    {
        echo json_encode($array);
    }
    
    public function sendError($e)
    {
        $this->send(array('error' => $e));
        exit();
    }

    public function useModel($modelname)
    {
        $filename = OrionContext::$PATH . Orion::MODULE_PATH . $this->name . '/' . $modelname. Orion::MODEL_EXT . '.php';

        if(!file_exists($filename))
            throw new OrionException('['.$modelname.'] Model file not found in module directory.', E_USER_WARNING, $this->name);
    
        require_once($filename);
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
        return (!OrionTools::startWith($function, '_')
                && OrionTools::match($function, $this->FUNCTION_NAME_MATCH)
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
