<?php

namespace Orion\Core\DB;


/**
 * \Orion\Core\DB\Base
 * 
 * Orion DB connector interface.
 *
 * Generates a singelton instance.
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 *
 * @author Thibaut Despoulain
 * @version 0.11.12
 *
 * @static
 */
interface Base
{
 
    public static function getConnection();
    
    public static function disconnect();

}

?>
