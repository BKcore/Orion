<?php

/**
 * Orion DB connector factory.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 *
 * @static
 */

namespace Orion\Core;

class DB
{

    /**
     * Return DB instance or create intitial connection
     * @return \Orion\Core\DB\Base
     * @access public
     */
    public static function getConnection()
    {
        if ( !\Orion::config()->get( 'DB_TYPE' ) )
            throw new Exception( 'No database type set in Orion configuration file.', E_ERROR, get_class() );

        try
        {
            $dbClass = '\\Orion\\Core\\DB\\' . ucfirst( strtolower( \Orion::config()->get( 'DB_TYPE' ) ) );
            return $dbClass::getConnection();
        }
        catch ( Exception $e )
        {
            throw new Exception( 'Unable to load DB class for [' . \Orion::config()->get( 'DB_TYPE' ) . '] database type.', E_ERROR, get_class() );
        }
    }

    /**
     * Manually close the PDO connection to database
     * @return boolean success
     */
    public static function disconnect()
    {
        if ( !\Orion::config()->get( 'DB_TYPE' ) )
            throw new Exception( 'No database type set in Orion configuration file.', E_ERROR, get_class() );

        try
        {
            $dbClass = 'DB\\' . ucfirst( strtolower( \Orion::config()->get( 'DB_TYPE' ) ) );
            return $dbClass::disconnect();
        }
        catch ( Exception $e )
        {
            throw new Exception( 'Unable to load DB class for [' . \Orion::config()->get( 'DB_TYPE' ) . '] database type.', E_ERROR, get_class() );
        }
    }

}

?>
