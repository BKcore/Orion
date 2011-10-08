<?php
/**
 * jQueryUI plugin class.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
namespace Orion\Plugins\JQuery;

use \Orion\Plugins;
use \Orion\Core;

class UI
{
    const CLASS_NAME = 'UI';

    const PLUGIN_DIR = 'ui/';

    const PLUGIN_JS = 'assets/jquery.ui.js';
    const PLUGIN_CSS = 'assets/jquery.ui.css';

    /**
     * Loads jQueryUI js/css files
     * @param mixed $args
     */
    public static function load(&$args)
    {
        try{
            Plugins\jQuery::loadPlugin(self::PLUGIN_DIR . self::PLUGIN_JS);
            Plugins\jQuery::loadCSS(self::PLUGIN_DIR . self::PLUGIN_CSS);
        }
        catch(Core\Exception $e)
        {
            throw $e;
        }
    }
}