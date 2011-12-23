<?php

namespace Orion\Core;


/**
 * \Orion\Core\Security
 * 
 * Orion security class.
 * 
 * Contains security-related methods, like password generator,
 * Injection escape, hashing, validation, etc.
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 *
 * @author Thibaut Despoulain
 * @version 0.11.12
 *
 * @static
 */
class Security
{
    const E_INVALID_JSON = 81;
    const E_INVALID_EXT = 82;
    const E_CSRF_FAIL = 83;
    const E_HTMLAWED_FAIL = 84;

    /**
     * Check CSRF token validity. Throws a Security\Exception if a CSRF attack is detected.
     * @param String $key The token identifier used in csrfGenerate, also the key of the token inside $origin
     * @param Mixed $origin The origin of the token to test (mostly $_POST or $_GET), but can also be a custom associative array. This array must contain the token under the key $key.
     * @param Boolean $forceExit Set this to TRUE to force the script to exit(1) if the CSRF check fails.
     */
    public static function csrfCheck( $key, $origin, $forceExit=false )
    {
        try
        {
            if ( !isset( $_SESSION[ 'csrf_' . $key ] ) )
                throw new \Exception( 'Missing CSRF session token.' );

            if ( !isset( $origin[ $key ] ) )
                throw new \Exception( 'Missing CSRF form token.' );

            // Get valid token from session
            $hash = $_SESSION[ 'csrf_' . $key ];
            // Free up session token for one-time CSRF token usage.
            $_SESSION[ 'csrf_' . $key ] = null;

            // Check if session token matches form token
            if ( $origin[ $key ] != $hash )
                throw new \Exception( 'Invalid CSRF token.' );

            // Check for token expiration
            if ( $timespan != null && is_int( $timespan ) && intval( substr( base64_decode( $hash ), 0, 10 ) ) + $timespan < time() )
                throw new \Exception( 'CSRF token has expired.' );
        }
        catch ( \Exception $e )
        {
            throw new Security\Exception( 'CSRF Check failed ! Please use the original form to send data.', self::E_CSRF_FAIL, $forceExit );
        }
    }

    /**
     * Generates a new anti-CSRF token and stores it in session for future check.
     * @param String $key The token identifier
     * @return Hash The token
     */
    public static function csrfGenerate( $key )
    {
        // token generation (basically base64_encode any random complex string, time() is used for token expiration) 
        $token = base64_encode( time() . self::genPassword( 32 ) );
        // store the one-time token in session
        $_SESSION[ 'csrf_' . $key ] = $token;

        return $token;
    }

    /**
     * Generates a random alphanumeric password
     * @param Integer $length Password length
     * @param String $custom String containing custom chars
     * @return string 
     */
    public static function genPassword( $length, $custom='' )
    {
        $seed = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijqlmnopqrtsuvwxyz0123456789' . $custom;
        $max = strlen( $seed ) - 1;

        $password = '';
        for ( $i = 0; $i < $length; ++$i )
        {
            $password .= $seed{intval( mt_rand( 0.0, $max ) )};
        }
        return $password;
    }

    /**
     * Process given string using the htmLawed algorithm. Deny risky HTML content.
     * @see <http://www.bioinformatics.org/phplabware/internal_utilities/htmLawed/>
     * @param String $input The text to process
     * @param Boolean $safemode Use builtin 'safe' configuration
     * @param Mixed $config Custom configuration array (extends default configuration)
     * @return String The processed text
     */
    public static function htmLawed( $input, $safemode=true, $config=null )
    {
        $localConfig = array( );

        if ( $safemode )
        {
            $localConfig[ 'safe' ] = 1;
            $localConfig[ 'deny_attribute' ] = 'style';
            $localConfig[ 'balance' ] = 0;
        }
        if ( $config != null && is_array( $config ) )
            array_merge( $localConfig, $config );

        try
        {
            include_once( Context::getLibsPath( 'htmLawed.php' ) );
            return htmLawed( $input, $localConfig );
        }
        catch ( \Exception $e )
        {
            if ( \Orion::isDebug() )
                throw $e;
            else
                throw new Security\Exception( 'An exception occured while trying to run security parsing using htmLawed.', self::E_HTMLAWED_FAIL );
        }
    }

    /**
     * Escapes a string to be put into htML to prevent SQL/JS injections
     * @param string $string
     * @return string
     */
    public static function preventInjection( $string )
    {
        return htmlspecialchars( $string );
    }

    /*
     * hash('md5', $data) shortcut
     */

    public static function md5Hash( $data )
    {
        if ( empty( $data ) )
            throw new Exception( 'Unable to hash provided string. String is empty.' );
        return hash( 'md5', $data );
    }

    /**
     * An elaborated split/double-salted hash method to hash passwords for example.
     * Uses sha1 as final hashing algorithm
     * @param string $data
     * @param string $extrasalt
     * @return hash
     */
    public static function saltedHash( $data, $extrasalt )
    {
        $password = str_split( $data, (strlen( $data ) / 2) + 1 );
        $hash = hash( 'sha1', $extrasalt . $password[ 0 ] . \Orion::config()->get( 'SECURITY_KEY' ) . $password[ 1 ] );
        return $hash;
    }

    /**
     * Removes risky parts from a standard file path (., .., empty) and normalise directory separators
     * @param string $path
     * @return string
     */
    public static function sanitizePath( $path )
    {
        $path = str_replace( array( '\\', '/' ), array( DS, DS ), $path );
        $p = explode( DS, $path );
        $out = $path{0} == DS ? array( '' ) : array( );
        foreach ( $p as $dir )
        {
            if ( $dir == '' || $dir == '.' || $dir == '..' )
                continue;

            $out[ ] = $dir;
        }
        return implode( DS, $out );
    }

    /**
     * Test if given filename uses on of the given extentions.
     * @param string $string The file name
     * @param string|string[] $ext The extention(s) (without the .)
     * @return boolean
     */
    public static function validateExtension( $string, $ext )
    {
        if ( is_string( $ext ) )
            $ext = array( $ext );

        return (preg_match( '/\.(?:' . implode( '|', $ext ) . ')$/six', $string ) > 0);
    }

    /**
     * Validates a JSON string.
     * @param string $data The json-encoded data
     * @return boolean
     */
    public static function validateJSON( $data )
    {
        $jsonregex = '/
                      (?(DEFINE)
                         (?<number>   -? (?= [1-9]|0(?!\d) ) \d+ (\.\d+)? ([eE] [+-]? \d+)? )    
                         (?<boolean>   true | false | null )
                         (?<string>    " ([^"\\\\]* | \\\\ ["\\\\bfnrt\/] | \\\\ u [0-9a-f]{4} )* " )
                         (?<array>     \[  (?:  (?&json)  (?: , (?&json)  )*  )?  \s* \] )
                         (?<pair>      \s* (?&string) \s* : (?&json)  )
                         (?<object>    \{  (?:  (?&pair)  (?: , (?&pair)  )*  )?  \s* \} )
                         (?<json>   \s* (?: (?&number) | (?&boolean) | (?&string) | (?&array) | (?&object) ) \s* )
                      )
                      \A (?&json) \Z
                      /six';
        return (preg_match( $jsonregex, $data ) > 0);
    }

}

?>
