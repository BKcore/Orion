<?php
/**
 * Orion main class.<br />
 * Makes everything work together
 * <p>Orion core classes must be placed in core/ sub directory and
 * respect Orion's class naming convention.<br/>
 * {lower(classname)}.orion.php</p>
 * 
 * <p>Copyright (c) 2008-2011, Thibaut Despoulain
 * All rights reserved.</p>
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
 * @copyright 2006-2011, Thibaut Despoulain
 * @link http://orion.bkcore.com/
 * @version 0.2.11
 *
 * @license BSD 4-clauses
 */
class Orion
{
    const CLASS_NAME = 'Orion';
    /**
     * Relative path to Orion's core classes
     */
    const CORE_PATH = 'core/';
    /**
     * Relative path to Orion's configuration files
     */
    const CONF_PATH = 'configs/';
    /**
     * Relative path to Orion's modules
     */
    const MODULE_PATH = 'modules/';
    /**
     * Relative path to Orion's plugins
     */
    const PLUGIN_PATH = 'plugins/';
    /**
     * Relative path to Orion's templates
     */
    const RENDERER_PATH = 'renderers/';
    /**
     * Orion's class extension
     */
    const CLASS_EXT = '.orion';
    /**
     * Orion's config extension
     */
    const CONF_EXT = '.config';
    /**
     * Orion's module extension
     */
    const MODULE_EXT = '.module';
    /**
     * Orion's model extension
     */
    const MODEL_EXT = '.model';
    /**
     * Orion's plugin extension
     */
    const PLUGIN_EXT = '.plugin';
    /**
     * Orion's renderer extension
     */
    const RENDERER_EXT = '.renderer';
    /**
     * Orion's template extension
     */
    const TEMPLATE_EXT = '.tpl';
    /**
     * Orion's view extension
     */
    const VIEW_EXT = '.view';
    /**
     * Orion's class suffix
     */
    const CLASS_SUFFIX = 'Orion';
    /**
     * Configuration class suffix
     */
    const CONF_SUFFIX = 'Config';
    /**
     * Module class suffix
     */
    const MODULE_SUFFIX = 'Module';
    /**
     * Plugin class suffix
     */
    const PLUGIN_SUFFIX = 'Plugin';
    /**
     * Template class suffix
     */
    const RENDERER_SUFFIX = 'Renderer';
    /**
     * Default mode
     */
    const MODE_DEFAULT = 'default';
    /**
     * Admin mode
     */
    const MODE_ADMIN = 'admin';

    /**
     * OrionConfig accessor variable, use Orion::config() or Orion::o->getConfig() to access it.
     * @var OrionConfig
     */
    private static $CONFIG=null;

    /**
     * OrionModule accessor variable
     * @var <? extends OrionModule>
     */
    private static $MODULE=null;

    /**
     * Path to orion's base directory ('orion/') by default
     * @var string
     */
    private static $BASE;

    /**
     * Orion's mode (Orion::MODE_DEFAULT | Orion::MODE_ADMIN)
     * @var string
     */
     private static $MODE = 'default';
     
    /**
     * The list of Orion's core classes
     * @var array<string>
     */
    private static $CLASSES = array('auth'
                                    ,'config'
                                    ,'context'
                                    ,'exception'
                                    ,'form'
                                    ,'model'
                                    ,'module'
                                    ,'plugin'
                                    ,'renderer'
                                    ,'route'
                                    ,'security'
                                    ,'sql'
                                    ,'tools');

    /**
     * Start the Orion instance.<br />
     * Register Orion's spl_autoload.
     * @param string $path Path to Orion's main directory. ('orion/' by default)<br/><b>With the trailing slash but without the first.</b>
     */
    public function  __construct($path)
    {
        self::$BASE = $path;

        spl_autoload_register('Orion::autoload');
    }

    /**
     * Load and init a new OrionConfig instance, linking it to Orion.
     * @param string $filename The configuration file to use (No path, no extension, just the name).
     */
    public function configure($filename)
    {
        if(self::$CONFIG == null)
        {
            $file = self::$BASE.self::CONF_PATH.$filename.self::CONF_EXT.".php";
            $class = ucfirst($filename).self::CONF_SUFFIX;
            if(file_exists($file))
            {
                require_once($file);
                self::$CONFIG = new $class();
                self::$CONFIG->load();
            }
            else throw new OrionException('Configuration file '.$filename.' does not exist.', E_USER_ERROR, self::CLASS_NAME);
        }
        else throw new OrionException('Cannot load more than one config file.', E_USER_WARNING, self::CLASS_NAME);
    }

    /**
     * Run everything, launch module, etc.
     */
    public function run()
    {
        if(self::$MODULE != null)
            throw new OrionException('Only one Orion instance is allowed at a time.', E_USER_ERROR, self::CLASS_NAME);
        
        OrionContext::init(self::$BASE);
        $module = OrionContext::$MODULE_NAME;
        $modulefile = self::$BASE.self::MODULE_PATH.$module.'/'.$module.'.'.self::$MODE.'.php';
        $moduleclass = ucfirst($module).self::MODULE_SUFFIX;

        if(!in_array($module, self::$CONFIG->get('OPEN_MODULES')))
            throw new OrionException('Module ['.$module.'] is not a trusted module (see OPEN_MODULES in configuration).', E_USER_ERROR, self::CLASS_NAME);

        if(!file_exists($modulefile))
            throw new OrionException('Module class file ('.$modulefile.') does not exist.', E_USER_ERROR, self::CLASS_NAME);

        require_once($modulefile);
        self::$MODULE = new $moduleclass();
        self::$MODULE->load();
    }

    /**
     * Autoloader for Orion's core classes
     * @param string $classname
     */
    public static function autoload($classname)
    {
        $filename = strtolower(str_replace(self::CLASS_SUFFIX, '', $classname));
        $file = self::$BASE.self::CORE_PATH.$filename.self::CLASS_EXT.'.php';
        if(in_array($filename, self::$CLASSES))
        {
            if(file_exists($file))
                require_once($file);
            else throw new Exception('Class file does not exist.', E_USER_ERROR);
        }
        //else throw new Exception('Trying to load an unregistered class.', E_USER_ERROR);
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
     * @return <? extends OrionConfig>
     */
    public static function &config()
    {
        return self::$CONFIG;
    }

    /**
     * Current module accessor
     * @return <? extends OrionModule>
     */
    public static function &module()
    {
        return self::$MODULE;
    }

    /**
     * Get important context data as an array (useful for template hydratation)
     */
    public static function getDataArray()
    {
        $array = array();
        $array['module'] = array();
        $array['module']['name'] = self::$MODULE->getName();
        $array['module']['path'] = OrionContext::getModulePath();
        $array['module']['url'] = OrionContext::getModuleURL(self::$MODULE->getName());
        $array['module']['uri'] = self::$MODULE->getName().OrionContext::$MODULE_EXT;
        $array['template'] = array();
        $array['template']['name'] = self::$MODULE->getTemplate();
        $array['template']['path'] = OrionContext::getTemplatePath(self::$MODULE->getTemplate());
        $array['template']['abspath'] = OrionContext::getTemplateAbsolutePath(self::$MODULE->getTemplate());
        if(self::$CONFIG->defined(strtoupper(self::getMode()) . '_MENU'))
            $array['menu'] = self::$CONFIG->get(strtoupper(self::getMode()) . '_MENU');
        $array['title'] = self::$CONFIG->get('SITE_NAME');
        $array['baseurl'] = self::$CONFIG->get('BASE_URL');
        $array['mode'] = self::$MODE;

        return $array;
    }

    /**
     * Set Orion's mode ('main'|'admin'). You can use Orion::MODE_DEFAULT or Orion::MODE_ADMIN constants.
     * <p>Modes are used to determinate which menu and context to use</p>
     * Default mode is 'default'
     * @example If mode is set to Orion::MODE_<MODE>, the menu will be loaded with Orion::config()->get('<MODE>_MENU');
     * @param string Mode
     */
    public static function setMode($mode)
    {
        self::$MODE = strtolower($mode);
    }

    /**
     * Get Orion's mode ('default'|'admin'). You can use Orion::MODE_DEFAULT or Orion::MODE_ADMIN constants.
     * @return string Mode
     */
    public static function getMode()
    {
        return self::$MODE;
    }

}
?>
