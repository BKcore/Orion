<?php

namespace Orion\Core;


/**
 * \Orion\Core\Config
 * 
 * Orion configuration handler class.
 * Handles configuration files loading and variable accessing
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 *
 * @author Thibaut Despoulain
 * @version 0.11.12
 */
abstract class Config
{

    /**
     * The loaded configuration keys
     * @var array<string, mixed>
     */
    private $data;

    /**
     * Sets configuration data keys and values
     * This method needs to be overriden in configuration file class
     */
    abstract public function load();

    public function __construct()
    {
        $data = array( );
    }

    /**
     * Check if provided key has been defined in current configuration
     * @param string $key
     * @return boolean
     */
    public function defined( $key )
    {
        return (array_key_exists( $key, $this->data ));
    }

    /**
     * Get provided key's value from current configuration.
     * There is an internal check for key's existance, which throws an Exception if the key is not defined.
     * @param string $key
     * @return mixed
     */
    public function get( $key )
    {
        if ( !array_key_exists( $key, $this->data ) )
        {
            throw new Exception( 'Unknown configuration key [' . $key . '].', E_USER_WARNING, get_class() );
            return null;
        }

        return $this->data[ $key ];
    }

    /**
     * Define a key/value pair inside current configuration.
     * Use this method inside the overriden load() method of configuration file to define new conf keys.
     * @param mixed $key
     * @param mixed $value
     */
    protected function set( $key, $value )
    {
        if ( !is_string( $key ) )
            throw new Exception( 'Configuration key must be a string.', E_WARNING, get_class() );

        $this->data[ $key ] = $value;
    }

}

?>