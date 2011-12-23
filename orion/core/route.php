<?php
namespace Orion\Core;

/**
 * \Orion\Core\Route
 * 
 * Orion Route class
 *
 * Handles routing rules and URI parsing.
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 *
 * @author Thibaut Despoulain
 * @version 0.11.12
 */
class Route
{
    /**
     * The 1024 error is passed as argument to the error method when no matching rule is found.
     */
    const E_NORULE = 1024;

    /**
     * Default method to be called if no rule match is found or if module is called without a route.
     * /!\ This method name must be given WITHOUT the security prefix, like you would declare it inside of a route rule.
     * By default, 'index' is used.
     * @var string
     */
    private $defaultMethod = 'index';

    /**
     * Method called when no rule matches the URI.
     * @var string 
     */
    private $errorMethod = 'error';

    /**
     * Array of routing rules.
     * The form is array($methodname => regex)
     * @var array
     */
    private $rules = array();


    /**
     * Decode current request URI into a usable RouteMethod.<br />
     * It's then possible to retreive the method to call and the arguments via the following getters:<br />
     * RouteMethod->getMethod();<br />
     * RouteMethod->getArgs();<br />
     * RouteMethod->getURI();<br />
     *
     * @return RouteMethod
     * @see RouteMethod
     */
    public function decode()
    {
        $uri = Context::getModuleURI();

        $matches = array();
        
        if($uri == '' || $uri == '/')
            return new RouteMethod($this->defaultMethod, null, $uri);

        foreach($this->rules as $regex => $method)
        {
            if(preg_match(Tools::translateRegex($regex).'six', $uri, $matches))
            {
                array_shift($matches);
                return new RouteMethod($method, $matches, $uri);
            }
        }

        return new RouteMethod($this->errorMethod, array(self::E_NORULE), $uri);
        
    }
	
    /**
    * Decode current request URI into a usable OrionRouteMethod using an automated parser.<br />
    * It's then possible to retreive the method to call and the arguments via the following getters:<br />
    * OrionRouteMethod->getMethod();<br />
    * OrionRouteMethod->getArgs();<br />
    * OrionRouteMethod->getURI();<br />
    *
    * @return OrionRouteMethod
    * @see OrionRouteMethod
    */
    public function decodeAuto()
    {
        $uri = Context::$MODULE_URI;
        $matches = array();

        if($uri == '' || $uri == '/')
            return new RouteMethod($this->defaultMethod, null, $uri);


        if(preg_match('/^([a-zA-Z0-9_-]+)\/(.*)$/i', $uri, $matches))
        {
            array_shift($matches);
            $method = array_shift($matches);
            $args = array_shift($matches);

            if(\Orion::config()->defined('ROUTE_AUTO_ARGSEP'))
                $cargs = explode(\Orion::config()->get('ROUTE_AUTO_ARGSEP'), $args);
            else
                $cargs = array($args);

            return new RouteMethod($method, $cargs, $uri);
        }
        elseif(preg_match('/^([a-zA-Z0-9_-]+)$/i', $uri, $matches))
        {
            array_shift($matches);
            $method = array_shift($matches);
            return new RouteMethod($method, null, $uri);
        }

        return new RouteMethod($this->errorMethod, array(self::E_NORULE), $uri);
    }

    /**
     * Adds a new routing rule to parse URIs.
     * Rules are identified by their regex, thus, it's impossible to have the same regex for two different functions.<br />
     * <b>'index' method rule (blank regex) is implicit and thus doesn't needs to be added, but you can still define a 'index.html' rule mapped to the _index function.</b> Note that while the rule is implicit, the function itself as to be manualy overriden in the module.
     * @param string $regex
     * 		Must be either a PCRE regex or a custom Easy-regex<br />
     * 		Example : 'article-?-@-*.html' is the Easy-regex equivalent to '#article\-([a-zA-Z0-9_-]+)\-(\d+)-(.*?)\.html$#')
     * 		If the method has a dynamic number of arguments or facultative arguments, you have to add a rule for each possible URI.
     * @param string $method the method name to be called (without any security character, see OrionModule callback for more)
	 */
    public function addRule($regex, $method)
    {
        $this->rules[$regex] = $method;
    }

    /**
     * Set the default method to be called with empty or '/' URI.
     * @param string $method
     */
    public function setDefaultMethod($method)
    {
        $this->defaultMethod = $method;
    }

    /**
     * Set the error method to be called if no rule match is found or if module is called without a route.
     * @param string $method
     */
    public function setErrorMethod($method)
    {
        $this->errorMethod = $method;
    }

    /**
     * Get defined rules
     * @return array Rules
     */
    public function getRules()
    {
        return $this->rules;
    }

}
/**
 * OrionRoute subclass Method.
 * Handles OrionRoute decode response.
 *
 * @author Thibaut Despoulain
 */
class RouteMethod
{
	/**
	 * Decoded method name (without any security escaping character like the default '_')
	 * @var string
	 */
	private $name;

	/**
	 * Decoded method args, to use with call_user_func_array($function, $param_array)
	 * @var array<mixed>
	 */
	private $args;

	/**
	 * The decoded URI
	 * @var string
	 */
	private $uri;

	/**
	 * Builds an OrionRouteMethod response used to call rule-linked methods
	 * @param string $_method
	 * @param array<mixed> $_args
	 * @param string $_uri
	 */
	public function  __construct($_name, $_args, $_uri)
	{
		$this->name = $_name;
		$this->args = $_args;
		$this->uri = $_uri;
	}

	/**
	 * Get the decoded method name (without any security escaping character like the default '_')
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get the decoded method args, to use with call_user_func_array($function, $param_array)
	 * @return array<mixed>
	 */
	public function getArgs()
	{
		return $this->args;
	}

	/**
	 * Get the decoded URI
	 * @return string
	 */
	public function getURI()
	{
		return $this->uri;
	}
}

?>
