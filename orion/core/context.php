<?php

namespace Orion\Core;


/**
 * \Orion\Core\Context
 * 
 * Orion context class.
 *
 * Handles static context variables such as URL and requests.
 * Support for URL generation, header definitions and more.
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 *
 * @author Thibaut Despoulain
 * @version 0.2.11
 *
 * @static
 */
class Context
{
    /**
     * Current requested URI
     * @var string
     */
    public static $URI;

    /**
     * Base site directory, with starting and trailing slashes
     * @var string
     */
    public static $BASE_DIR;

    /**
     * Relative path to Orion's directories ('orion/' by default)
     * @var string
     */
    public static $PATH;

    /**
     * Current module name
     * @var string
     */
    public static $MODULE_NAME = null;

    /**
     * Module URI extension ('.o' by default)
     * @var string
     */
    public static $MODULE_EXT = null;

    /**
     * Modules sub URI used for in-module routing purpose
     * @var string
     */
    public static $MODULE_URI = '';

    /**
     * Module separator (after module slug and before sub URI)
     */
    public static $MODULE_SEP = '/';

    /**
     * Inits Orion's URI context.
     * Basically retreiving and parsing : base directory, requested URI, module URI, URI parameters, and mode.
     * 
     * Throw an Exception if no compatible URI is found.
     */
    public static function init( $path )
    {
        try
        {
            self::$URI = $_SERVER[ 'REQUEST_URI' ];

            if ( \Orion::config()->defined( 'BASE_DIR' ) )
                self::$BASE_DIR = \Orion::config()->get( 'BASE_DIR' );
            else
                self::$BASE_DIR = '';

            if ( \Orion::config()->defined( 'MODULE_SEPARATOR' ) )
                self::$MODULE_SEP = \Orion::config()->get( 'MODULE_SEPARATOR' );
            else
                self::$MODULE_SEP = '/';

            self::$PATH = $path;
            $uri = self::getRelativeURI();
            $modelist = \Orion::config()->get( 'MODE_LIST' );

            if ( $uri == '' )
            {
                $mode = \Orion::config()->get( 'DEFAULT_MODE' );

                if ( !array_key_exists( $mode, $modelist ) )
                    throw new Exception( "Default mode isn't registered in MODE_LIST", E_USER_ERROR, get_class() );

                \Orion::setMode( $mode );
                self::$MODULE_EXT = $modelist[ $mode ];
                self::$MODULE_NAME = \Orion::config()->get( 'DEFAULT_MODULE' );
                self::$MODULE_URI = '';
            }
            else
            {
                foreach ( $modelist as $mode => $ext )
                {
                    $matches = array( );
                    // Module-only URI type (ex: module.html)
                    if ( preg_match( '#^(\w+)' . Tools::escapeRegex( $ext ) . '$#', $uri, $matches ) )
                    {
                        \Orion::setMode( $mode );
                        self::$MODULE_EXT = $ext;
                        self::$MODULE_NAME = $matches[ 1 ];
                        self::$MODULE_URI = null;
                        break;
                    }
                    // Module+Parameters URI type (ex: module/some/more/parameters.html)
                    elseif ( preg_match( '#^(\w+)' . self::$MODULE_SEP . '(.*)' . Tools::escapeRegex( $ext ) . '$#', $uri, $matches ) )
                    {
                        \Orion::setMode( $mode );
                        self::$MODULE_EXT = $ext;
                        self::$MODULE_NAME = $matches[ 1 ];
                        self::$MODULE_URI = $matches[ 2 ];
                        break;
                    }
                }
            }

            // No compatible URI found, redirecting.
            if ( self::$MODULE_NAME == null )
            {
                Context::redirect( 404 );
            }
        }
        catch ( Exception $e )
        {
            throw $e;
        }
    }

    /**
     * Write redirect header
     * @param mixed $url Either a redirect code or an URL
     */
    public static function redirect( $url )
    {
        if ( $url == 404 )
        {
            if ( \Orion::isDebug() )
                die( 'Requested URL failed. Components were : ' . self::$MODULE_EXT . ' : ' . self::$MODULE_NAME . ' : ' . self::$MODULE_URI );
            else
            {
                $links = \Orion::config()->get( 'URL_404' );
                if ( !array_key_exists( \Orion::getMode(), $links ) )
                    $mode = 'default';
                else
                    $mode = \Orion::getMode();
                self::setHeaderCode( 404 );
                die( file_get_contents( $links[ $mode ] ) );
                $exit( 1 );
            }
        }
        else
        {
            die( header( 'Location: ' . $url ) );
        }
    }

    /**
     * Generates an absolute URL (prevents URL rewrite issues)
     * @param string page url (ie: home/page-2.html)
     */
    public static function genURL( $page )
    {
        return \Orion::config()->get( 'BASE_URL' ) . $page;
    }

    /**
     * Generates an absolute module URL from a module name. 
     * For example: genModuleURL('home','/page-1','admin'); will generate 'http://mysite.com/pathtoapp/home/page-1.html'
     * @param string $module Example: home
     * @param string $uri Example: /page/1
     * @param string $mode Example admin
     * @return string
     */
    public static function genModuleURL( $module, $uri=null, $mode=null )
    {
        $modelist = \Orion::config()->get( 'MODE_LIST' );

        if ( ($mode) != null && array_key_exists( $mode, $modelist ) )
            $ext = $modelist[ $mode ];
        else
            $ext = self::$MODULE_EXT;

        if ( $uri == null || empty( $uri ) )
            $page = $module . $ext;
        else
            $page = $module . self::$MODULE_SEP . $uri . $ext;

        return Context::genURL( $page );
    }

    /**
     * Get the absolute URL based on an URL relative to the base URL
     * @param String Relative URL
     * @return String
     */
    public static function getAbsoluteURL( $relativeURL )
    {
        // if URL is already absolute
        if ( Tools::match( $relativeURL, '(https?|ftp|www\.)(.*)' ) )
            return $relativeURL;

        // removes any heading slash
        if ( $relativeURL{0} == DS )
            $relativeURL = substr( $relativeURL, 1 );

        return self::getBaseURL() . $relativeURL;
    }

    /**
     * Get Orion's base URL (this is an alias for Orion::config()->get('BASE_URL'))
     * @return string
     */
    public static function getBaseURL()
    {
        return \Orion::config()->get( 'BASE_URL' );
    }

    /**
     * Get current mode's default template from configuration
     * @return string template name
     */
    public static function getDefaultTemplate()
    {
        $template = \Orion::config()->get( strtoupper( \Orion::getMode() ) . '_TEMPLATE' );

        if ( is_null( $template ) )
            $template = \Orion::config()->get( 'DEFAULT_TEMPLATE' );

        return $template;
    }

    /**
     * Get default mode's extension from configuration file
     * @return string Extension
     */
    public static function getDefaultModeExtension()
    {
        $list = \Orion::config()->get( 'MODE_LIST' );
        return $list[ \Orion::config()->get( 'DEFAULT_MODE' ) ];
    }

    /**
     * Get the relative URI (Base URI minus BASE_DIR)
     * @return string The relative URI
     */
    public static function getRelativeURI()
    {
        if ( Tools::startWith( self::$URI, self::$BASE_DIR ) )
            return substr( self::$URI, strlen( self::$BASE_DIR ) );
        else
            return self::$URI;
    }

    /**
     * Get current module's relative URI (ex: 'home')
     * @return string
     */
    public static function getModuleURI()
    {
        return self::$MODULE_URI;
    }

    /**
     * Get current page complete relative URI (ex: 'module/method/params.html')
     * @return string
     */
    public static function getFullURI()
    {
        if ( empty( self::$MODULE_URI ) )
            return self::$MODULE_NAME . self::$MODULE_EXT;
        else
            return self::$MODULE_NAME . self::$MODULE_SEP . self::$MODULE_URI . self::$MODULE_EXT;
    }

    /**
     * Get current page complete absolute URL (ex: 'mysite.com/module/method/params.html')
     * @return string
     */
    public static function getFullURL()
    {
        return \Orion::config()->get( 'BASE_URL' ) . self::getFullURI();
    }

    /**
     * Get module's complete url
     * @param string $module Module name, if NULL, returns current module's URL
     * @return string
     */
    public static function getModuleURL( $module=null )
    {
        if ( $module == null )
            return self::genModuleURL( self::$MODULE_NAME );
        else
            return self::genModuleURL( $module );
    }

    /**
     * Get current module's path (with trailing slash)
     * @return string
     */
    public static function getGlobalModelPath( $model=null )
    {
        if ( $model == null )
            return \Orion::base() . \Orion::MODEL_PATH;
        else
            return \Orion::base() . \Orion::MODEL_PATH . $model . \Orion::MODEL_EXT . '.php';
    }

    /**
     * Get Orion's lib path or generate a filepath for a libs component.
     * @param String $file optionnal
     * @return String
     */
    public static function getLibsPath( $file='' )
    {
        return \Orion::base() . \Orion::LIBS_PATH . $file;
    }

    /**
     * Get Orion's absolute lib path or generate a filepath for a libs component.
     * @param String $file optionnal
     * @return String
     */
    public static function getLibsAbsolutePath( $file='' )
    {
        return \Orion::config()->get( 'BASE_URL' ) . self::getLibsPath( $file );
    }

    /**
     * Get current module's path (with trailing slash)
     * @return string
     */
    public static function getModulePath()
    {
        return \Orion::base() . \Orion::MODULE_PATH . self::$MODULE_NAME . DS;
    }

    /**
     * Get current module's absolute path (with trailing slash)
     * @return string
     */
    public static function getModuleAbsolutePath( $file='' )
    {
        return \Orion::config()->get( 'BASE_URL' ) . \Orion::base() . \Orion::MODULE_PATH . self::$MODULE_NAME . DS . $file;
    }

    /**
     * Get plugin path (with trailing slash)
     * @param the plugin folder, if null, returns the global plugins directory path
     * @return string
     */
    public static function getPluginPath( $plugin=null )
    {
        if ( $plugin == null )
            return \Orion::base() . \Orion::PLUGIN_PATH;
        else
            return \Orion::base() . \Orion::PLUGIN_PATH . $plugin . DS;
    }

    /**
     * Get plugin's complete url
     * @param string $plugin plugin file, if NULL, returns global plugin url
     * @return string
     */
    public static function getPluginURL( $plugin=null )
    {
        if ( $plugin == null )
            return \Orion::config()->get( 'BASE_URL' ) . \Orion::base() . \Orion::PLUGIN_PATH;
        else
            return \Orion::config()->get( 'BASE_URL' ) . \Orion::base() . \Orion::PLUGIN_PATH . $plugin . DS;
    }

    /**
     * Get the relative path to the provided template folder (with trailing slash)
     * @param string $template Template name
     * @return string
     */
    public static function getTemplatePath( $template )
    {
        return \Orion::config()->get( 'TEMPLATE_PATH' ) . $template . DS;
    }

    /**
     * Get the relative path of an uploaded file
     * @param string $file 
     */
    public static function getUploadRelativeFilePath( $file='' )
    {
        return \Orion::config()->get( 'UPLOAD_DIR' ) . \Orion::config()->get( 'FILE_UPLOAD_DIR' ) . $file;
    }

    /**
     * Get the absolute path of an uploaded file
     * @param string $file 
     */
    public static function getUploadPath( $file='' )
    {
        return self::getBaseURL() . \Orion::config()->get( 'UPLOAD_DIR' ) . $file;
    }

    /**
     * Get the absolute path of an uploaded file in the upload_file folder
     * @param string $file 
     */
    public static function getUploadFilePath( $file='' )
    {
        return self::getUploadPath( \Orion::config()->get( 'FILE_UPLOAD_DIR' ) . $file );
    }

    /**
     * Get the full path to the provided template folder
     * @param string $template Template name
     * @return string
     */
    public static function getTemplateAbsolutePath( $template )
    {
        return \Orion::config()->get( 'TEMPLATE_ABS_PATH' ) . $template . DS;
    }

    /**
     * Get the full path to the provided template file
     * @param string $template Template name
     * @return string
     */
    public static function getTemplateFilePath( $template )
    {
        return self::getTemplatePath( $template ) . $template . \Orion::TEMPLATE_EXT;
    }

    /**
     * Get a $_GET variable
     * @param $var the variable name
     * @param $exceptIfNotSet Set this to TRUE to throw an exception if the variable is not set
     * @param $preventInjection Set this to TRUE to escape variable content to prevent injections
     */
    public static function get( $var, $exceptIfNotSet, $preventInjection )
    {
        if ( !isset( $_GET[ $var ] ) )
        {
            if ( $exceptIfNotSet )
                throw new Exception( '"' . Security::prevenInjection( $var ) . '" is not set in get data.' );
            else
                return null;
        }
        else
        {
            return ($preventInjection ? Security::prevenInjection( $_GET[ $var ] ) : $_GET[ $var ]);
        }
    }

    /**
     * Get a $_POST variable
     * @param $var the variable name
     * @param $exceptIfNotSet Set this to TRUE to throw an exception if the variable is not set
     * @param $preventInjection Set this to TRUE to escape variable content to prevent injections
     */
    public static function post( $var, $exceptIfNotSet=false, $preventInjection=false )
    {
        if ( !isset( $_POST[ $var ] ) )
        {
            if ( $exceptIfNotSet )
                throw new Exception( '"' . Security::preventInjection( $var ) . '" is not set in post data.' );
            else
                return null;
        }
        else
        {
            return ($preventInjection ? Security::preventInjection( $_POST[ $var ] ) : $_POST[ $var ]);
        }
    }

    /**
     * Write provided status code and corresponding text into output headers.
     * Range of supported code is 100-504.
     * @param int $code 
     */
    public static function setHeaderCode( $code )
    {
        $http = array(
            100 => "HTTP/1.1 100 Continue",
            101 => "HTTP/1.1 101 Switching Protocols",
            200 => "HTTP/1.1 200 OK",
            201 => "HTTP/1.1 201 Created",
            202 => "HTTP/1.1 202 Accepted",
            203 => "HTTP/1.1 203 Non-Authoritative Information",
            204 => "HTTP/1.1 204 No Content",
            205 => "HTTP/1.1 205 Reset Content",
            206 => "HTTP/1.1 206 Partial Content",
            300 => "HTTP/1.1 300 Multiple Choices",
            301 => "HTTP/1.1 301 Moved Permanently",
            302 => "HTTP/1.1 302 Found",
            303 => "HTTP/1.1 303 See Other",
            304 => "HTTP/1.1 304 Not Modified",
            305 => "HTTP/1.1 305 Use Proxy",
            307 => "HTTP/1.1 307 Temporary Redirect",
            400 => "HTTP/1.1 400 Bad Request",
            401 => "HTTP/1.1 401 Unauthorized",
            402 => "HTTP/1.1 402 Payment Required",
            403 => "HTTP/1.1 403 Forbidden",
            404 => "HTTP/1.1 404 Not Found",
            405 => "HTTP/1.1 405 Method Not Allowed",
            406 => "HTTP/1.1 406 Not Acceptable",
            407 => "HTTP/1.1 407 Proxy Authentication Required",
            408 => "HTTP/1.1 408 Request Time-out",
            409 => "HTTP/1.1 409 Conflict",
            410 => "HTTP/1.1 410 Gone",
            411 => "HTTP/1.1 411 Length Required",
            412 => "HTTP/1.1 412 Precondition Failed",
            413 => "HTTP/1.1 413 Request Entity Too Large",
            414 => "HTTP/1.1 414 Request-URI Too Large",
            415 => "HTTP/1.1 415 Unsupported Media Type",
            416 => "HTTP/1.1 416 Requested range not satisfiable",
            417 => "HTTP/1.1 417 Expectation Failed",
            500 => "HTTP/1.1 500 Internal Server Error",
            501 => "HTTP/1.1 501 Not Implemented",
            502 => "HTTP/1.1 502 Bad Gateway",
            503 => "HTTP/1.1 503 Service Unavailable",
            504 => "HTTP/1.1 504 Gateway Time-out"
        );
        header( $http[ $code ] );
    }

}

?>
