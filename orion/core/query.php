<?php

/**
 * Orion Query factory.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.11.10
 */

namespace Orion\Core;

class Query
{
    const ASCENDING = 'ASC';
    const DESCENDING = 'DESC';
    const EQUAL = '=';
    const LIKE = 'LIKE';
    const NOT = 'NOT';

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
