<?php
/**
 * jQuery Fancybox plugin class.
 * Handles static context variables such as URL, Language and more
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class jQueryFancyBoxPlugin
{
    const CLASS_NAME = 'jQueryFancyBoxPlugin';

    const FB_DIR = 'fancybox/';

    const FB_JS = 'assets/jquery.fancybox.js';
    const FB_CSS = 'assets/jquery.fancybox.css';

    public static function load(&$args)
    {
        try{
            jQueryPlugin::loadPlugin(self::FB_DIR . self::FB_JS);
            jQueryPlugin::loadCSS(self::FB_DIR . self::FB_CSS);
            jQueryPlugin::script("
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
            });");
        }
        catch(OrionException $e)
        {
            throw $e;
        }
    }
}