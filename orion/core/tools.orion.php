<?php
/**
 * Orion tools class.
 * Various function helpers
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class OrionTools
{
    /**
     * Regex styntax characters (escaped version)
     * @var array
     */
    private static $_REGEX_ESCAPED = array('\-', '\#', '\!', '\^', '\$', '\(', '\)', '\[', '\]', '\{', '\}', '\+', '\*', '\.', '\|');

    /**
     * Regex styntax characters (unescaped version)
     * @var array
     */
    private static $_REGEX_UNESCAPED = array('-', '#', '!', '^', '$', '(', ')', '[', ']', '{', '}', '+', '*', '.', '|');

    /**
     * Easy regex wildcards characters
     * @var array
     */
    private static $_REGEX_WILDCARDS = array('?', '@');

    /**
     * Easy regex wildacards traduction in PCRE
     * @var array
     */
    private static $_REGEX_WCREPLACE = array('(\w+)', '(\d+)');

    /**
     * SQL wildards unescaped
     * @var array
     */
    private static $_SQL_UNESCAPED = array('%', '_');

    /**
     * SQL wildards escaped
     * @var array
     */
    private static $_SQL_ESCAPED = array('\%', '\_');

    /**
     * <p>Call a class method with $args as arguments.
     * This is a more effective version of call_user_func_array() for 5 or less arguments.</p>
     * <p>If $args is of size 6 or more, call_user_func_array is called instead</p>
     * @param mixed $class The class instance
     * @param string $method The method name
     * @param array<mixed> $args An array of arguments
     */
    public static function callClassMethod(&$class, $method, $args=null)
    {
        //echo 'Calling... ['.count($args).']'.$method;
        switch(count($args)) {
            case 0: $class->{$method}(); break;
            case 1: $class->{$method}($args[0]); break;
            case 2: $class->{$method}($args[0], $args[1]); break;
            case 3: $class->{$method}($args[0], $args[1], $args[2]); break;
            case 4: $class->{$method}($args[0], $args[1], $args[2], $args[3]); break;
            case 5: $class->{$method}($args[0], $args[1], $args[2], $args[3], $args[4]); break;
            default: call_user_func_array(array($class, $method), $args);  break;
        }
    }

    /**
     * Escape special regex chars.
     * In fact, this function is only usefull with an easy-regex instance or in very particular cases.
     * @param string $regex Regex to escape (without starting and ending delimiters and special chars that must remain special chars) 
     * @return string Escaped regex
     */
    public static function escapeRegex($regex)
    {
        return str_replace(self::$_REGEX_UNESCAPED, self::$_REGEX_ESCAPED,
					str_replace(self::$_REGEX_ESCAPED, self::$_REGEX_UNESCAPED, $regex));
    }

    /**
     * Escapes sql wildcards (% and _) (prevents some SQL injection methods)
     * @param string $string SQL string
     * @return escaped string
     */
    public static function escapeSql($string)
    {
        return str_replace(self::$_SQL_UNESCAPED, self::$_SQL_ESCAPED,
					str_replace(self::$_SQL_ESCAPED, self::$_SQL_UNESCAPED, $string));
    }

	/**
	 * Extract sub array from base array, keeping only keys starting with $pattern
	 * @param array<string,mixed> $array
	 * @param string $pattern
	 * @return array<key, mixed>
	 */
	public static function extractArrayKeysStartingWith($array, $pattern)
	{
		$out = array();
		
		foreach($array as $key => $value)
		{
			if(OrionTools::startWith($key, $pattern))
				$out[$key] = $value;
		}
		
		return $out;
	}
	
    /**
     * preg_match shortcut.
     *
     * @param String Input to test
     * @param String Regex pattern without start/end tags (ex: "[a-zA-Z]+")
     *
     * @return TRUE if $string matches $pattern, FALSE otherwise.
     */
    public static function match($string, $pattern)
    {
        return preg_match('#^'.$string.'$#');
    }

    /**
     * Test if provided string starts with provided expression.
     *
     * @param String Input to test
     * @param String Starting expression
     *
     * @return TRUE if $string starts with $start, FALSE otherwise.
     */
    public static function startWith($string, $start)
    {
        return (substr($string, 0, strlen($start)) == $start);
    }

    /**
     * Translates an Easy-regex into a valid PCRE regex.
     * @param string $regex PCRE or Easy-regex.
     * @return string PCRE-valid regex
     */
	public static function translateRegex($regex)
	{
		if(substr($regex,0,1) == '#')
			return $regex;
		else
			return '#'.str_replace(self::$_REGEX_WILDCARDS, self::$_REGEX_WCREPLACE,
						str_replace(self::$_REGEX_UNESCAPED, self::$_REGEX_ESCAPED,
						str_replace(self::$_REGEX_ESCAPED, self::$_REGEX_UNESCAPED,
						$regex))).'$#';
	}
}
?>
