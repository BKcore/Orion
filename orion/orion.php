<?php

/**
 * Orion main class.<br />
 * Makes everything work together
 * 
 * <p>Usage : $o = new Orion(); $o->configure('main'); $o->run();
 * 
 * <p>Copyright (c) 2010-2012, Thibaut Despoulain
 * All rights reserved.
 * http://orion.bkcore.com/</p>
 *
 * <p>Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this software
 *    must display the following acknowledgement:
 *    This product includes software developed by the <organization>.
 * 4. Neither the name of the <organization> nor the
 *    names of its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written permission.</p>
 *
 * <p>THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.</p>
 * 
 * 
 * @author Thibaut Despoulain
 * @copyright 2010-2012, Thibaut Despoulain
 * @link http://orion.bkcore.com/
 * @version 0.11.10
 *
 * @license BSD 4-clauses
 */
define( 'FS', '.' );
if( !defined( 'DS' ) ) define( 'DS', DIRECTORY_SEPARATOR );

class Orion
{
    const BASE_NS = '\\Orion\\';
    const CONF_NS = '\\Orion\\Configs\\';
    const MODULE_NS = '\\Orion\\Modules\\';
    const PLUGIN_NS = '\\Orion\\Plugins\\';
    const MODEL_NS = '\\Orion\\Models\\';
    /**
     * Class base
     */
    const CLASS_BASE = 'Orion';
    /**
     * Relative path to Orion's core classes
     */
    const CORE_PATH = 'core/';
    /**
     * Relative path to Orion's configuration files
     */
    const CONF_PATH = 'configs/';
    /**
     * Reslative path to Orion's third-party libs
     */
    const LIBS_PATH = 'libs/';
    /**
     * Relative path to Orion's global models
     */
    const MODEL_PATH = 'models/';
    /**
     * Relative path to Orion's modules
     */
    const MODULE_PATH = 'modules/';
    /**
     * Relative path to Orion's plugins
     */
    const PLUGIN_PATH = 'plugins/';
    /**
     * Relative path to Orion's renderers
     */
    const RENDERER_PATH = 'renderers/';
    /**
     * Orion's local model extension
     */
    const MODEL_EXT = '.model';
    /**
     * Orion's template extension
     */
    const TEMPLATE_EXT = '.tpl';
    /**
     * Orion's view extension
     */
    const VIEW_EXT = '.view';
    /**
     * Default mode
     */
    const MODE_DEFAULT = 'default';

    /**
     * Set Orion's debug mode
     * @var boolean
     */
    private static $DEBUG = false;

    /**
     * OrionConfig accessor variable, use Orion::config() or Orion::o->getConfig() to access it.
     * @var Orion\Core\Config
     */
    private static $CONFIG = null;

    /**
     * Module controller accessor variable
     * @var Orion\Core\Controller
     */
    private static $MODULE = null;

    /**
     * Path to orion's base directory ('orion/' by default)
     * @var string
     */
    private static $BASE;

    /**
     * Orion's mode 
     * @var string
     */
    private static $MODE = 'default';

    /**
     * Start the Orion instance.<br />
     * Register Orion's spl_autoload.
     * @param string $path Path to Orion's main directory. ('orion/' by default)<br/><b>With the trailing slash but without the first.</b>
     */
    public function __construct( $path='orion/' )
    {
        self::$BASE = $path;
        date_default_timezone_set('Europe/Berlin');
        spl_autoload_register('Orion::autoload');
    }

    /**
     * Load and init a new OrionConfig instance, linking it to Orion.
     * @param string $filename The configuration file to use (No path, no extension, just the name).
     */
    public function configure( $filename )
    {
        if ( self::$CONFIG == null )
        {
            $class = self::CONF_NS . ucfirst( $filename );
            try
            {
                self::$CONFIG = new $class();
                self::$CONFIG->load();
            } catch ( Exception $e )
            {
                throw new Orion\Core\Exception( 'Configuration file does not exist.', E_USER_ERROR, get_class( $this ) );
            }
        }
        else
            throw new Orion\Core\Exception( 'Cannot load more than one config file.', E_USER_WARNING, get_class( $this ) );
    }

    /**
     * Run everything, launch module, etc.
     */
    public function run()
    {
        if ( self::$MODULE != null )
            throw new Orion\Core\Exception( 'Only one Orion instance is allowed at a time.', E_USER_ERROR, get_class( $this ) );

        Orion\Core\Context::init( self::$BASE );

        $module = Orion\Core\Context::$MODULE_NAME;
        $modulefile = self::$BASE . self::MODULE_PATH . $module . DS . $module . FS . self::$MODE . '.php';
        $moduleclass = self::MODULE_NS . ucfirst( $module ) . '\\' . ucfirst( $module ) . ucfirst( self::$MODE );

        if ( !in_array( $module, self::$CONFIG->get( 'OPEN_MODULES' ) ) )
            Orion\Core\Context::redirect( 404 );
        //throw new Orion\Core\Exception('Module ['.$module.'] is not a trusted module (see OPEN_MODULES in configuration).', E_USER_ERROR, get_class($this));

        if ( !file_exists( $modulefile ) )
        //Orion\Core\Context::redirect (404);
            throw new Orion\Core\Exception( 'Module class file (' . $modulefile . ') does not exist.', E_USER_ERROR, get_class( $this ) );

        require_once($modulefile);
        self::$MODULE = new $moduleclass();
        self::$MODULE->load();
    }

    /**
     * Autoloader for Orion's core classes
     * @param string $classname
     */
    public static function autoload( $classname )
    {
        try
        {
            $file = self::parseClassName( $classname );
        } catch ( Exception $e )
        {
            return false;
        }

        if ( file_exists( $file ) )
            require_once($file);
        else
            return false;
        //throw new Exception('Class file does not exist.', E_USER_ERROR);
    }

    /**
     * Parse a class name and transform it into its corresponding path
     * @param string $name of the class
     */
    public static function parseClassName( $name )
    {
        // replace NS separator by DS separator and add extension
        $name = str_replace( '\\', '/', strtolower( $name ) ) . '.php';

        // remove any heading slash
        if ( $name{0} == '/' )
            $name = substr( $name, 1 );

        // change root NS directory if Orion is not in its default directory
        if ( substr( $name, 0, 6 ) == 'orion/' && self::$BASE != 'orion/' )
            $name = self::$BASE . substr( $name, 6 );

        return $name;
    }

    /**
     * @return string orion's base dir with the trailing slash. ex: orion/
     */
    public static function base()
    {
        return self::$BASE;
    }

    /**
     * Config class accessor
     * @return Orion\Core\Config
     */
    public static function &config()
    {
        return self::$CONFIG;
    }

    /**
     * Current module accessor
     * @return Orion\Core\Controller
     */
    public static function &module()
    {
        return self::$MODULE;
    }

    /**
     * Set Orion's mode ('main'|'admin'). You can use Orion::MODE_DEFAULT or Orion::MODE_ADMIN constants.
     * <p>Modes are used to determinate which menu and context to use</p>
     * Default mode is 'default'
     * @example If mode is set to Orion::MODE_<MODE>, the menu will be loaded with Orion::config()->get('<MODE>_MENU');
     * @param string Mode
     */
    public static function setMode( $mode )
    {
        self::$MODE = strtolower( $mode );
    }

    /**
     * Get Orion's mode ('default'|'admin'). You can use Orion::MODE_DEFAULT or Orion::MODE_ADMIN constants.
     * @return string Mode
     */
    public static function getMode()
    {
        return self::$MODE;
    }

    public static function debug()
    {
        self::$DEBUG = true;
    }

    public static function isDebug()
    {
        return self::$DEBUG;
    }

}

?>
