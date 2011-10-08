<?php

/**
 * Orion DB connector interface.
 *
 * Generates a singelton PDO instance.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 *
 * @static
 */

namespace Orion\Core\DB;

interface Base
{
 
    public static function getConnection();
    
    public static function disconnect();

}

?>
