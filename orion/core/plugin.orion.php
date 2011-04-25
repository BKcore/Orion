<?php
/**
 * Orion plugin class.
 * Handles plugins load and usage
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class OrionPlugin
{
    const CLASS_NAME = 'OrionPlugin';

    private static $loaded = array();

    public static function load($plugin, $args=null)
    {
        $plist = explode('.', $plugin);
        
        $parsed = array();
        foreach($plist as $p)
        {
            $file = OrionContext::getPluginPath() . OrionTools::concatWithTrail(DIRECTORY_SEPARATOR, $parsed, true) . strtolower($p) . DIRECTORY_SEPARATOR . OrionTools::concatWithTrail('.', $parsed, true) . strtolower($p) . Orion::PLUGIN_EXT . '.php';
            $class = implode('', $parsed) . $p . Orion::PLUGIN_SUFFIX;
            $pname = OrionTools::concatWithTrail('.', $parsed, true) . $p;

            if(file_exists($file))
            {
                if(!in_array($pname, self::$loaded))
                {
                    require_once($file);

                    if(method_exists($class, 'load'))
                        call_user_func($class.'::load', $args);
                    
                    self::$loaded[] = $pname;
                }

                $parsed[] = $pname;
            }
            else throw new OrionException('Plugin ['.$p.'] file does not exists : '.$file, E_USER_WARNING, self::CLASS_NAME);
        }
        
    }
}