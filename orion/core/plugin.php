<?php

namespace Orion\Core;


/**
 * \Orion\Core\Plugin
 * 
 * Orion plugin class.
 * 
 * Handles plugins load and usage.
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 *
 * @author Thibaut Despoulain
 * @version 0.11.12
 * 
 * @deprecated With current namespacing autoloading, there should be no need for this class. It's only here to assure backward compatibility.
 *
 * @static
 */
class Plugin
{

    private static $loaded = array( );

    /**
     * Loads provided plugin and call its load() function with arguments $args
     * @param string $plugin
     * @param mixed $args
     */
    public static function load( $plugin, $args=null )
    {
        $plist = explode( '.', $plugin );

        if ( is_string( $plist ) )
            $plist = array( $plist );

        $parsed = array( );
        foreach ( $plist as $p )
        {
            $file = Context::getPluginPath() . Tools::concatWithTrail( DS, $parsed, true ) . strtolower( $p ) . DS . strtolower( $p ) . '.php';
            $class = \Orion::PLUGIN_NS . implode( '\\', $parsed ) . (!empty( $parsed ) ? '\\' : '') . $p;
            $pname = Tools::concatWithTrail( '.', $parsed, true ) . $p;


            if ( file_exists( $file ) )
            {
                if ( !in_array( $pname, self::$loaded ) )
                {
                    require_once($file);

                    try
                    {
                        if ( method_exists( $class, 'load' ) )
                            $class::load( $args );

                        self::$loaded[ ] = $pname;
                    }
                    catch ( Exception $e )
                    {
                        throw $e;
                    }
                }

                $parsed[ ] = $pname;
            }
            else
                throw new Exception( 'Plugin [' . $p . '] file does not exists : ' . $file, E_USER_WARNING, get_class() );
        }
    }

}