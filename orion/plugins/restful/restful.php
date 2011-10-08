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

class RESTful
{
    const CLASS_NAME = 'RESTful';

    /**
     *
     * @var OrionRenderer
     */
    private static $TPL = null;
    public static $path;

    /**
     * Adds default JS variables to current document (like module path, module URI, and vars)
     * @param mixed $args must contain a 'tpl' key with the current template object 
     * and can contain :
     *      a 'vars' key with an associative array to pass variables to JS, 
     *      an 'include' key with an array of local js files to load
     */
    public static function load($args)
    {
        if(!isset($args['tpl']) || $args['tpl'] == null)
            throw new Core\Exception('Plugin RESTful needs a template object as argument in $args["tpl"]', E_USER_ERROR, self::CLASS_NAME);

        self::$TPL =& $args['tpl'];
        
        $vars = '';
        if(is_array($args['vars']) && !empty($args['vars']))
            foreach($args['vars'] as $key => $val)
                $vars .= ','.$key.':"'.$val.'"';
        
        $script = '<script type="text/javascript">
                //<![CDATA[
                var RESTdata={module:"'.Core\Context::$MODULE_NAME.'",path:{root:"'.Core\Context::getBaseURL().'",module:"'.Core\Context::getModuleAbsolutePath().'",page:"'.Core\Context::getModuleURL().'"},vars:{_restv:"RESTfulPlugin.v1"'.$vars.'}};
                //]]>
                </script>';
        self::$TPL->addJs(trim($script));
        
        if(is_array($args['include']) && !empty($args['include']))
            foreach($args['include'] as $js)
                self::$TPL->includeJS(Core\Context::getModuleAbsolutePath().$js);
    }
}