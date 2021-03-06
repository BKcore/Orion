<?php

namespace Orion\Core;


/**
 * \Orion\Core\Tools
 * 
 * Orion tools class.
 *
 * Various function helpers used internally
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 *
 * @author Thibaut Despoulain
 * @version 0.11.12
 *
 * @static
 */
class Tools
{

    /**
     * Regex styntax characters (escaped version)
     * @var array
     */
    private static $_REGEX_ESCAPED = array( '\-', '\#', '\!', '\^', '\$', '\(', '\)', '\[', '\]', '\{', '\}', '\+', '\*', '\.', '\|' );

    /**
     * Regex styntax characters (unescaped version)
     * @var array
     */
    private static $_REGEX_UNESCAPED = array( '-', '#', '!', '^', '$', '(', ')', '[', ']', '{', '}', '+', '*', '.', '|' );

    /**
     * Easy regex wildcards characters
     * @var array
     */
    private static $_REGEX_WILDCARDS = array( '@', '?', '*' );

    /**
     * Easy regex wildcards saves (used to prevent regex escape from escaping the easyregex syntax)
     * @var array
     */
    private static $_REGEX_WSAVE = array( '::number::', '::word::', '::any::' );

    /**
     * Easy regex wildacards traduction in PCRE
     * @var array
     */
    private static $_REGEX_WCREPLACE = array( '(\d+)', '([a-zA-Z0-9_-]+)', '(.*?)' );

    /**
     * SQL wildards unescaped
     * @var array
     */
    private static $_SQL_UNESCAPED = array( '%', '_' );

    /**
     * SQL wildards escaped
     * @var array
     */
    private static $_SQL_ESCAPED = array( '\%', '\_' );

    /**
     * <p>Call a class method with $args as arguments.
     * This is a more effective version of call_user_func_array() for 5 or less arguments.</p>
     * <p>If $args is of size 6 or more, call_user_func_array is called instead</p>
     * @param mixed $class The class instance
     * @param string $method The method name
     * @param array<mixed> $args An array of arguments
     */
    public static function callClassMethod( &$class, $method, $args=null )
    {
        //echo 'Calling... ['.count($args).']'.$method;
        switch ( count( $args ) )
        {
            case 0: $class->{$method}();
                break;
            case 1: $class->{$method}( $args[ 0 ] );
                break;
            case 2: $class->{$method}( $args[ 0 ], $args[ 1 ] );
                break;
            case 3: $class->{$method}( $args[ 0 ], $args[ 1 ], $args[ 2 ] );
                break;
            case 4: $class->{$method}( $args[ 0 ], $args[ 1 ], $args[ 2 ], $args[ 3 ] );
                break;
            case 5: $class->{$method}( $args[ 0 ], $args[ 1 ], $args[ 2 ], $args[ 3 ], $args[ 4 ] );
                break;
            default: call_user_func_array( array( $class, $method ), $args );
                break;
        }
    }

    /**
     * Escape special regex chars.
     * In fact, this function is only usefull with an easy-regex instance or in very particular cases.
     * @param string $regex Regex to escape (without starting and ending delimiters and special chars that must remain special chars) 
     * @return string Escaped regex
     */
    public static function escapeRegex( $regex )
    {
        return str_replace( self::$_REGEX_UNESCAPED, self::$_REGEX_ESCAPED, str_replace( self::$_REGEX_ESCAPED, self::$_REGEX_UNESCAPED, $regex ) );
    }

    /**
     * Escapes sql wildcards (% and _) (prevents some SQL injection methods)
     * @param string $string SQL string
     * @return escaped string
     */
    public static function escapeSql( $string )
    {
        return str_replace( self::$_SQL_UNESCAPED, self::$_SQL_ESCAPED, str_replace( self::$_SQL_ESCAPED, self::$_SQL_UNESCAPED, $string ) );
    }

    /**
     * Extract sub array from base array, keeping only keys starting with $pattern
     * @param array<string,mixed> $array
     * @param string $pattern
     * @return array<key, mixed>
     */
    public static function extractArrayKeysStartingWith( $array, $pattern )
    {
        $out = array( );

        foreach ( $array as $key => $value )
        {
            if ( Tools::startWith( $key, $pattern ) )
                $out[ $key ] = $value;
        }

        return $out;
    }

    /**
     * Get an array of files contained in a directory (recursively)
     * @param string $directory The directory to scan recursively.
     * @return string[] An array of file paths
     */
    public static function getFiles( $directory )
    {
        if ( substr( $directory, -1 ) == DS )
        {
            $directory = substr( $directory, 0, -1 );
        }
        
        if( !file_exists( $directory ) || !is_dir( $directory ) )
            throw new Exception( 'Directory ['.Security::preventInjection ( $directory ).'] does not exist, unable to get files.' );
        
        $base = $directory;
        $directoryHandle = opendir( $directory );
        
        $arr = array();

        while ( $contents = readdir( $directoryHandle ) )
        {
            if ( $contents != '.' && $contents != '..' )
            {
                $path = $directory . DS . $contents;

                if ( is_dir( $path ) )
                {
                    $files = self::getFiles( $base.DS.$contents );
                    foreach($files as $file)
                        $arr[] = $file;
                }
                else
                {
                    $arr[] = $base.DS.$contents;
                }
            }
        }

        closedir( $directoryHandle );
        return $arr;
    }
    
    /**
     * preg_match shortcut.
     *
     * @param String Input to test
     * @param String Regex pattern without start/end tags (ex: "[a-zA-Z]+")
     *
     * @return TRUE if $string matches $pattern, FALSE otherwise.
     */
    public static function match( $string, $pattern, $modifiers='' )
    {
        return preg_match( '#^' . $pattern . '$#' . $modifiers, $string );
    }

    /**
     * Concats an array with $trail after each item (Useful for path and file array to string)
     * @param string $trail
     * @param array $array
     * @param boolean $tolower Should each item be lower case'd ?
     * @return string
     */
    public static function concatWithTrail( $trail, $array, $tolower=false )
    {
        if ( $array == null || empty( $array ) )
            return '';

        $out = '';
        foreach ( $array as $item )
        {
            $out .= ($tolower ? strtolower( $item ) : $item) . $trail;
        }
        return $out;
    }

    /**
     * Removes one or more characters from a string.
     * @param String|String[] $needle
     * @param String $string
     * @return String
     */
    public static function removeString( $needle, $string )
    {
        return str_replace( $needle, '', $string );
    }

    /**
     * Test if provided string starts with provided expression.
     *
     * @param String Input to test
     * @param String Starting expression
     *
     * @return TRUE if $string starts with $start, FALSE otherwise.
     */
    public static function startWith( $string, $start )
    {
        return (substr( $string, 0, strlen( $start ) ) == $start);
    }

    /**
     * Translates an Easy-regex into a valid PCRE regex.
     * @param string $regex PCRE or Easy-regex.
     * @return string PCRE-valid regex
     */
    public static function translateRegex( $regex )
    {
        if ( substr( $regex, 0, 1 ) == '#' )
            return $regex;
        else
            return '#^' . str_replace( self::$_REGEX_WILDCARDS, self::$_REGEX_WCREPLACE, str_replace( self::$_REGEX_WSAVE, self::$_REGEX_WILDCARDS, str_replace( self::$_REGEX_ESCAPED, self::$_REGEX_UNESCAPED, str_replace( self::$_REGEX_UNESCAPED, self::$_REGEX_ESCAPED, str_replace( self::$_REGEX_WILDCARDS, self::$_REGEX_WSAVE, $regex ) ) ) ) ) . '$#';
    }

}

?>
