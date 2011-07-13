<?php
/**
 * jQuery plugin class.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class DisqusPlugin
{
    const CLASS_NAME = 'DisqusPlugin';    

    private static $TPL = null;

    /**
     * Loads and include jQuery js file from google's CDN
     * @param mixed $args must contain a 'tpl' key with the current template object
     */
    public static function load(&$args)
    {
        if(!isset($args['tpl']) || $args['tpl'] == null)
            throw new OrionException('Plugin Disqus needs a template object as argument in $args["tpl"]', E_USER_ERROR, self::CLASS_NAME);
        if(!isset($args['id']) || $args['id'] == null)
            throw new OrionException('Plugin Disqus needs a page identifier as argument in $args["id"]', E_USER_WARNING, self::CLASS_NAME);

        $shortname = $args['shortname'] == null ? Orion::config()->get('DISQUS_SHORTNAME') : $args['shortname'];

		$dev = $args['dev'] == true ? "var disqus_developer = 1;" : "";
		
        if($shortname == null)
            throw new OrionException('Plugin Disqus needs a shortname string as argument in $args["shortname"] or in configuration under ["DISQUS_SHORTNAME"].', E_USER_ERROR, self::CLASS_NAME);
			
        self::$TPL =& $args['tpl'];
        
        $script = '<script type="text/javascript">
                    var disqus_shortname = "'.$shortname.'";
                    var disqus_identifier = "'.$args['id'].'";
                    var disqus_url = "'.$args['permalink'].'";
					'.$dev.'
                    (function() {
                        var dsq = document.createElement("script"); dsq.type = "text/javascript"; dsq.async = true;
                        dsq.src = "http://" + disqus_shortname + ".disqus.com/embed.js";
                        (document.getElementsByTagName("head")[0] || document.getElementsByTagName("body")[0]).appendChild(dsq);
                    })();
                </script>';

        self::$TPL->addJs($script);
        
        self::$TPL->assign('disqus_message', '<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>');
    }
}