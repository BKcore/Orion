<?php
/**
 * jQuery plugin class.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
namespace Orion\Plugins;

use \Orion\Core;

class jQuery
{
    const CLASS_NAME = 'jQuery';

    const JQUERY_FILE = 'jquery.min.js'; // 'http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js';

    private static $TPL = null;
    public static $path;

    /**
     * Loads and include jQuery js file from google's CDN
     * @param mixed $args must contain a 'tpl' key with the current template object
     */
    public static function load($args)
    {
        if(!isset($args['tpl']) || $args['tpl'] == null)
            throw new Core\Exception('Plugin jQuery needs a template object as argument in $args["tpl"]', E_USER_ERROR, self::CLASS_NAME);

        self::$TPL =& $args['tpl'];
        
        self::$TPL->includeJS(Core\Context::getPluginURL('jquery') . self::JQUERY_FILE);
    }

    /**
     * Adds inline js script to output view. script will be automatically enclosed in document.ready jQuery function if whenDocumentReady is set to true.
     * @param string $script
     * @param boolean
     */
    public static function script($script, $whenDocumentReady=false)
    {
        if(self::$TPL == null)
            throw new Core\Exception('Template object must be defined in load function before calling script()', E_USER_WARNING, self::CLASS_NAME);

        if($whenDocumentReady)
        {
            $script = '<script type="text/javascript">
                //<![CDATA[
                $(document).ready(function(){
                    '.$script.'
                });
                //]]>
                </script>';
        }
        else
        {
            $script = '<script type="text/javascript">
                //<![CDATA[
                    '.$script.'
                //]]>
                </script>';
        }
        self::$TPL->addJs($script);
    }

    /**
     * Loads a jQuery sub plugin js file like FancyBox for example
     * @param string $file
     * @param boolean $external Is the file external ? if false, automatically adds jQuery plugin path
     */
    public static function loadPlugin($file, $external=false)
    {
        if(self::$TPL == null)
            throw new Core\Exception('Template object must be defined in load function before calling loadPlugin()', E_USER_WARNING, self::CLASS_NAME);
        
        if(!$external)
            $file = Core\Context::getPluginURL('jquery') . $file;

        self::$TPL->includeJS($file);
    }

    /**
     * Loads a jQuery sub plugin CSS file
     * @param string $file
     * @param boolean $external Is the file external ? if false, automatically adds jQuery plugin path
     */
    public static function loadCSS($file, $external=false)
    {
        if(self::$TPL == null)
            throw new Core\Exception('Template object must be defined in load function before calling loadCSS()', E_USER_WARNING, self::CLASS_NAME);
        
        if(!$external)
            $file = Core\Context::getPluginURL('jquery') . $file;

        self::$TPL->includeCSS($file);
    }
}