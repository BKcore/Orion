<?php
/**
 * jQuery blockui plugin class.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
namespace Orion\Plugins\JQuery;

use \Orion\Plugins;
use \Orion\Core;

class BlockUI
{
    const CLASS_NAME = 'BlockUI';

    const DIR = 'blockui/';

    const JS = 'assets/jquery.blockui.js';

    /**
     * .
     * @param mixed $args
     */
    public static function load($args)
    {
        try{
            Plugins\jQuery::loadPlugin(self::DIR . self::JS);
        }
        catch(Core\Exception $e)
        {
            throw $e;
        }
    }
}