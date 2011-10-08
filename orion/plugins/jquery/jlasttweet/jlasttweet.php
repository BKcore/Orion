<?php
/**
 * jQuery jLastTweet plugin class.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
namespace Orion\Plugins\JQuery;

use \Orion\Plugins;
use \Orion\Core;

class jLastTweet
{
    const DIR = 'jlasttweet/';

    const JS = 'assets/jquery.jlasttweet.js';

    /**
     * .
     * @param mixed $args
     */
    public static function load($args)
    {
        try{
            $target = '#twitter-thread';
            $loader = '#twitter-loader';
            $data = array();
            
            if(is_array($args))
            {
                if(array_key_exists('target', $args))
                    $target = $args['target'];
                if(array_key_exists('loader', $args))
                    $loader = $args['loader'];
                if(array_key_exists('data', $args))
                    foreach($args['data'] as $opt => $value)
                        array_push($data, "'".$opt."':'".$value."'");
            }
            
            Plugins\jQuery::loadPlugin(self::DIR . self::JS);
            Plugins\jQuery::script("$('".$target."').jLastTweet({".implode(',', $data)."}, '".$loader."');", true);
        }
        catch(Core\Exception $e)
        {
            throw $e;
        }
    }
}