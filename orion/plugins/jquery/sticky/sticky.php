<?php
/**
 * jQuery Sticky plugin class.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
namespace Orion\Plugins\JQuery;

use \Orion\Plugins;
use \Orion\Core;

class Sticky
{
    const CLASS_NAME = 'jQueryStickyPlugin';

    const PLUGIN_DIR = 'sticky/';

    const PLUGIN_JS = 'assets/sticky.min.js';
    const PLUGIN_CSS = 'assets/sticky.min.css';

    /**
     * Loads Sticky js/css files
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