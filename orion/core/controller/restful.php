<?php
/**
 * Orion RESTful controller base class.
 *
 * Extend this class to create a new REST controller.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
namespace Orion\Core\Controller;

use \Orion\Core;

abstract class Restful extends Core\Controller
{
    const CLASS_NAME = 'OrionControllerRestful';
    
    const E_LOGIN_ERROR = 2;
    const E_LOGIN_DISALLOW = 4;
    const E_ROUTE_NO = 8;
    const E_FUNCTION_NO = 16;
    
    const DELETE = 'DELETE';
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';

    /**
     * Main module function, executed right after module loading by Orion.
     * Handles route parsing and function callbacks.
     */
    public function load()
    {
        if($this->route == null)
            $this->sendError(self::E_ROUTE_NO);

        $function = $this->route->decode();

        if(Core\Tools::startWith($function->getName(), '__'))
            $this->sendError(self::E_FUNCTION_NO);

        if(Core\Tools::startWith($function->getName(), self::FUNCTION_PREFIX))
            $this->sendError(self::E_FUNCTION_NO);

        if(!is_callable(array($this, self::FUNCTION_PREFIX.$function->getName())))
            $this->sendError(self::E_FUNCTION_NO);

        Core\Tools::callClassMethod($this, self::FUNCTION_PREFIX.$function->getName(), $function->getArgs());
    }

    /**
     * Allows access only to logged users that have a level equal to or less than provided role. If permission is not granted, it will send a JSON error object.
     * <p><b>Note that while it's doing all login/auth/redirection work automatically, you still have to create the corresponding user table in your database in addition to provide the login module into orion's module directory.</b></p>
     * @see OrionAuth
     *      MainConfig
     *      LoginModule
     * @param string $slug the role identifier (ie: 'administrator', 'member', etc.). See your configuration file for a liste of roles and their permission level.
     */
    public function allow($slug)
    {
        try {
            if(!Core\Auth::login(true))
            {
                $this->sendError(self::E_LOGIN_DISALLOW);
            }
            if(!Core\Auth::allow($slug))
            {// this exception prevents any redirection defect or hack
                $this->sendError(self::E_LOGIN_DISALLOW);
            }
        }
        catch(Core\Exception $e)
        {
            throw $e;
        }
    }
	
	/**
	 * Gets REST PUT data
	 */
	public function getPutData()
	{
		$data = null;
        parse_str(file_get_contents("php://input"), $data);
        return $data;
	}
    
	/**
	 * Test method used to access the resource
	 * @param $method POST|GET|PUT|DELETE
	 */
    public function isMethod($method)
    {
        return ($_SERVER['REQUEST_METHOD'] == $method);
    }
    
    /**
     * Encodes $array to JSON format and sends it.
     * @param array $array
     * @param boolean $exit Exit after response ?
     */
    public function send($array, $exit=true, $code=null)
    {
        $this->respond(json_encode($array), $exit, $code);
    }
    
    /**
     * Sends a standard {"error":X} JSON Object
     * @param int $e Error code
     */
    public function sendError($e, $code=401)
    {
        Core\Context::setHeaderCode($code);
        $this->send(array('error' => $e));
        exit();
    }
}
?>
