<?php
/**
 * jQuery Fancybox plugin class.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
namespace Orion\Plugins\JQuery;

use \Orion\Plugins;
use \Orion\Core;

class FancyBox
{
    const CLASS_NAME = 'FancyBox';

    const FB_DIR = 'fancybox/';

    const FB_JS = 'assets/jquery.fancybox.js';
    const FB_CSS = 'assets/jquery.fancybox.css';

    /**
     * Loads FancyBox js/css files and apply fancybox() to a[rel]'s
     * @param mixed $args
     */
    public static function load($args)
    {
        try{
            Plugins\jQuery::loadPlugin(self::FB_DIR . self::FB_JS);
            Plugins\jQuery::loadCSS(self::FB_DIR . self::FB_CSS);
            Plugins\jQuery::script("
            $('a[rel=fancybox-frame]').fancybox({
                'width'				: '50%',
                'height'			: '50%',
                'autoScale'     	: false,
                'transitionIn'		: 'none',
                'transitionOut'		: 'none',
                'type'				: 'iframe'
            });
            $('a[rel=fancybox-image]').fancybox({
                'transitionIn'		: 'none',
                'transitionOut'		: 'none',
                'titlePosition' 	: 'over'
            });", true);
        }
        catch(Core\Exception $e)
        {
            throw $e;
        }
    }
}