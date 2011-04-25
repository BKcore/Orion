<?php
/**
 * jQuery plugin class.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class jQueryPlugin
{
    const CLASS_NAME = 'jQueryPlugin';

    const JQUERY_FILE = 'http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js';

    private static $TPL = null;
    public static $path;

    /**
     * Loads and include jQuery js file from google's CDN
     * @param mixed $args must contain a 'tpl' key with the current template object
     */
    public static function load(&$args)
    {
        if(!isset($args['tpl']) || $args['tpl'] == null)
            throw new OrionException('Plugin jQuery needs a template object as argument in $args["tpl"]', E_USER_ERROR, self::CLASS_NAME);

        self::$TPL =& $args['tpl'];
        echo "##".self::JQUERY_FILE;
        self::$TPL->includeJS(self::JQUERY_FILE);
    }

    /**
     * Adds inline js script to output view. script will be automatically enclosed in document.ready jQuery function.
     * @param string $script
     */
    public static function script($script)
    {
        if(self::$TPL == null)
            throw new OrionException('Template object must be defined in load function before calling script()', E_USER_WARNING, self::CLASS_NAME);

        $script = '<script type="text/javascript">
            $(document).ready(function(){
                '.$script.'
            });
            </script>';
        self::$TPL->addJs($script);
    }

    /**
     * Loads a jQuery sub plugin js file like FancyBox for example
     * @param string $file
     */
    public static function loadPlugin($file)
    {
        if(self::$TPL == null)
            throw new OrionException('Template object must be defined in load function before calling loadPlugin()', E_USER_WARNING, self::CLASS_NAME);
        
        $file = OrionContext::getPluginURL('jquery') . $file;

        self::$TPL->includeJS($file);
    }

    /**
     * Loads a jQuery sub plugin CSS file
     * @param string $file
     */
    public static function loadCSS($file)
    {
        if(self::$TPL == null)
            throw new OrionException('Template object must be defined in load function before calling loadCSS()', E_USER_WARNING, self::CLASS_NAME);

        $file = OrionContext::getPluginURL('jquery') . $file;

        self::$TPL->includeCSS($file);
    }
}