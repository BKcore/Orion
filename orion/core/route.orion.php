<?php
/**
 * Orion template class.
 * Creates a standard Smarty instance
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class OrionRoute
{
    /**
     * Constants
     */
    const CLASS_NAME = 'OrionRoute';
    const E_NORULE = 1024;

    /**
     * Default method to be called if no rule match is found or if module is called without a route.
     * By default, 'index' is used.
     * @var string
     */
    private $defaultMethod = 'index';

    private $errorMethod = 'error';

    /**
     * Array of routing rules.
     * The form is array($methodname => regex)
     * @var array
     */
    private $rules = array();


    /**
     * Decode current request URI into a usable OrionRouteMethod.<br />
     * It's then possible to retreive the method to call and the arguments via the following getters:<br />
     * OrionRouteMethod->getMethod();<br />
     * OrionRouteMethod->getArgs();<br />
     * OrionRouteMethod->getURI();<br />
     *
     * @return OrionRouteMethod
     * @see OrionRouteMethod
     */
    public function decode()
    {
        //$orion = Orion::o();
        $uri = OrionContext::$MODULE_URI;
        $matches = array();

        if($uri == '' || $uri == '/')
            return new OrionRouteMethod($this->defaultMethod, null, $uri);

        foreach($this->rules as $regex => $method)
        {
            if(preg_match(OrionTools::translateRegex($regex), $uri, $matches))
            {
                array_shift($matches);
                return new OrionRouteMethod($method, $matches, $uri);
            }
        }

        return new OrionRouteMethod($this->errorMethod, array(self::E_NORULE), $uri);

        
    }

    /**
     * Adds a new routing rule to parse URIs.
     * Rules are identified by their regex, thus, it's impossible to have the same regex for two different functions.<br />
     * <b>'index' method rule (blank regex) is implicit and thus doesn't needs to be added, but you can still define a 'index.html' rule mapped to the _index function.</b> Note that while the rule is implicit, the function itself as to be manualy overriden in the module.
     * @param string $regex
     * Must be either a PCRE regex or a custom Easy-regex<br />
     * Example : 'article-?-@.html' is the Easy-regex equivalent to '#article\-(\w+)\-(\d+)\.html$#')<br />
     * See http://bkcore.com/labs/php_easy_regex.article.html
     * @param string $method the method name to be called (without any security character, see OrionModule callback for more)
     * @example If the method has a dynamic number of arguments or facultative arguments, you have to add a rule for each possible URI.<br />Example: for function _article($id, $page=null) {};<br />You have to use :<br />$route->addRule('article-?.html', 'article');<br />$route->addRule('article-?-@.html', 'articles');
     */
    public function addRule($regex, $method)
    {
        if(array_key_exists($regex, $this->rules))
            throw new OrionException("Duplicate entries in routing rules", E_USER_WARNING, self::CLASS_NAME);
        
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
 * @license MIT
 * @version 0.1
 */
class OrionRouteMethod
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
