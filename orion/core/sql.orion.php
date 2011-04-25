<?php
/**
 * Orion sql connector class.
 * Generates a singelton PDO instance.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class OrionSql
{
    const CLASS_NAME = 'OrionSql';
    
    /**
     * PDO instance
     * @var Object (PDO)
     */
    private static $instance = null;

    /**
     * Default SQL driver for PDO connector
     * @var string Must be a valid SQL driver
     * @see OrionSql::$SQL_DRIVERS
     */
    private static $DEFAULT_DRIVER = 'mysql';

    /**
     * List of valid SQL drivers
     * @var array<string> PDO drivers list
     * @see PDO
     */
    private static $SQL_DRIVERS = array('mysql','pgsql');

    /**
    * the constructor is set to private
    * so nobody can create a new instance using new
    */
    private function __construct()
    {
    }

    /**
    * Return PDO instance or create intitial connection
    * @return PDO
    * @access public
    */
    public static function getConnection()
    {
        if (!self::$instance)
        {
            try {
                $config = Orion::config();
                
                if(in_array($config->get('SQL_DRIVER'), self::$SQL_DRIVERS))
                    $driver = $config->get('SQL_DRIVER');
                else
                    $driver = self::$DEFAULT_DRIVER;
                
                self::$instance = new PDO($driver.":host=".$config->get('SQL_HOST').";dbname=".$config->get('SQL_DBNAME'), $config->get('SQL_USER'), $config->get('SQL_PASSWORD'));
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            catch(PDOException $e)
            {
                throw new OrionException($e->getMessage(), $e->getCode(), self::CLASS_NAME);
            }
            catch(OrionException $e)
            {
                throw $e;
            }
        }
        return self::$instance;
    }
	
	/**
	 * Manually close the PDO connection to database
	 * @return boolean success
	 */
	public static function disconnect()
	{
		if(self::$instance != null)
			self::$instance = null;
		
		return (self::$instance == null);
	}

    /**
    * __clone is set to private, so nobody can clone the instance
    */
    private function __clone()
    {
    }

}
?>
