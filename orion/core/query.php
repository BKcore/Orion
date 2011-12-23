<?php

namespace Orion\Core;


/**
 * \Orion\Core\Query
 * 
 * Orion Query factory.
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 *
 * @author Thibaut Despoulain
 * @version 0.11.12
 */
class Query
{
    // The following constants are used as keys to translates comparators in DB-specific query classes.
    const ASCENDING = 'ASC';
    const DESCENDING = 'DESC';
    const EQUAL = '=';
    const NEQUAL = '!=';
    const LIKE = 'LIKE';
    const NOT = 'NOT';
    const REGEX = 'REGEX';

    /**
     * (Factory) Create and return a new Query instance based on Query\Base and specific to the DB type set in configuration.
     * @param String $class Name of the model class from whom the Factory call originates (Facultative).
     * @return \Orion\Core\Query\Base
     */
    public static final function Factory( $class=null )
    {
        if ( !\Orion::config()->get( 'DB_TYPE' ) )
            throw new Exception( 'No database type set in Orion configuration file.', E_ERROR, get_class() );

        try
        {
            $queryClass = '\\Orion\\Core\\Query\\' . ucfirst( strtolower( \Orion::config()->get( 'DB_TYPE' ) ) );
            return new $queryClass( $class );
        }
        catch ( Exception $e )
        {
            throw new Exception( 'Unable to load Query class for [' . \Orion::config()->get( 'DB_TYPE' ) . '] database type.', E_ERROR, get_class() );
        }
    }

}

?>
