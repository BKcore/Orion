<?php
/**
 * Orion context class.
 * Handles static context variables such as URL, Language and more
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class OrionContext
{
    const CLASS_NAME = 'OrionContext';
    /**
     * Current requested URI
     * @var string
     */
    public static $URI;
    /**
     * Base site directory, with starting and trailing slashes
     * @example 'http://mysite.com/path/to/app/index.o' => '/path/to/app/'
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
    public static $MODULE_NAME=null;
    /**
     * Module URI extension ('.o' by default)
     * @var string
     */
    public static $MODULE_EXT=null;
    /**
     * Modules sub URI used for in-module routing purpose
     * @var string
     */
    public static $MODULE_URI='';

    /**
     * Inits Orion's URI context
     */

    public static function init($path)
    {
        try {
            self::$URI = $_SERVER['REQUEST_URI'];
            self::$BASE_DIR = Orion::config()->get('BASE_DIR');
            self::$PATH = $path;
            $uri = self::getRelativeURI();
            $modelist = Orion::config()->get('MODE_LIST');

            if($uri == '')
            {
                $mode = Orion::config()->get('DEFAULT_MODE');

                if(!array_key_exists($mode, $modelist))
                    throw new OrionException("Default mode isn't registered in MODE_LIST", E_USER_ERROR, self::CLASS_NAME);

                Orion::setMode($mode);
                self::$MODULE_EXT = $modelist[$mode];
                self::$MODULE_NAME = Orion::config()->get('DEFAULT_MODULE');
                self::$MODULE_URI = '';
            }
            else
            {
                foreach($modelist as $mode => $ext)
                {
                    $matches = array();
                    if(preg_match('#^(\w+)'.OrionTools::escapeRegex($ext).'(.*)$#', $uri, $matches))
                    {
                        Orion::setMode($mode);
                        self::$MODULE_EXT = $ext;
                        self::$MODULE_NAME = $matches[1];
                        self::$MODULE_URI = $matches[2];
                        break;
                    }
                }
            }

            if(is_null(self::$MODULE_NAME))
            {
                OrionContext::redirect(404);
            }
        }
        catch(OrionException $e)
        {
            throw $e;
        }
    }

    /**
     * Write redirect header
     * @param mixed $url Either a redirect code or an URL
     */

    public static function redirect($url)
    {
        if($url == 404)
            $target = Orion::config()->get('URL_404');
        else
            $target = $url;

        header('Location: '.$target);
    }

    /**
     * Generates an absolute URL (prevents URL rewrite issues)
     * @param string page url (ie: home.o/page/2)
     */
    public static function genURL($page)
    {
        return Orion::config()->get('BASE_URL').$page;
    }

    /**
     * Generates an absolute module URL from a module name
     * @param string $module Example: home
     * @param string $uri Example: /page/1
     * @param string $mode Example admin
     * @return string
     * @example genModuleURL('home','/page/1','admin'); will generate 'http://mysite.com/pathtoapp/home.a/page/1'
     */
    public static function genModuleURL($module, $uri=null, $mode=null)
    {
        $modelist = Orion::config()->get('MODE_LIST');

        if(!is_null($mode) && array_key_exists($mode, $modelist))
            $ext = $modelist[$mode];
        else
            $ext = self::$MODULE_EXT;

        return OrionContext::genURL($module.$ext.$uri);
    }

    /**
     * Get current mode's default template from configuration
     * @return string template name
     */
    public static function getDefaultTemplate()
    {
        $template = Orion::config()->get(strtoupper(Orion::getMode()).'_TEMPLATE');

        if(is_null($template))
            $template = Orion::config()->get('DEFAULT_TEMPLATE');

        return $template;
    }

    /**
     * Get the relative URI (Base URI minus BASE_DIR)
     * @return string The relative URI
     */
    public static function getRelativeURI()
    {
        if(OrionTools::startWith(self::$URI, self::$BASE_DIR))
            return substr(self::$URI, strlen(self::$BASE_DIR));
        else
            return self::$URI;
    }

    /**
     * Get current module's relative URI (ex: 'home.o')
     * @return string
     */
    public static function getModuleURI()
    {
        return self::$MODULE_NAME.self::$MODULE_EXT;
    }

    /**
     * Get module's complete url
     * @param string $module Module name, if NULL, returns current module's URL
     * @return string
     */
    public static function getModuleURL($module=null)
    {
        if(is_null($module))
            return Orion::config()->get('BASE_URL') . self::$MODULE_NAME . self::$MODULE_EXT;
        else
            return Orion::config()->get('BASE_URL') . $module . self::$MODULE_EXT;
    }

    /**
     * Get current module's path
     * @return string
     */
    public static function getModulePath()
    {
        return Orion::base().Orion::MODULE_PATH.self::$MODULE_NAME.'/';
    }

    /**
     * Get the relative path to the provided template folder
     * @param string $template Template name
     * @return string
     */
    public static function getTemplatePath($template)
    {
        return Orion::config()->get('TEMPLATE_PATH').$template.'/';
    }

    /**
     * Get the full path to the provided template folder
     * @param string $template Template name
     * @return string
     */
    public static function getTemplateAbsolutePath($template)
    {
        return Orion::config()->get('TEMPLATE_ABS_PATH').$template.'/';
    }

    /**
     * Get the full path to the provided template file
     * @param string $template Template name
     * @return string
     */
    public static function getTemplateFilePath($template)
    {
        return Orion::config()->get('TEMPLATE_PATH').$template.'/'.$template.Orion::TEMPLATE_EXT;
    }
}
?>
