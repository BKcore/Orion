<?php
/**
 * Orion exception class.
 * Extends standard Exception but with caller class handler
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
abstract class OrionConfig
{
    protected $CLASS_NAME;

    private $data;

    abstract public function load();

    public function  __construct()
    {
        $data = array();
    }

    public function defined($key)
    {
        return (array_key_exists($key, $this->data));
    }

    public function get($key)
    {
        if(!array_key_exists($key, $this->data))
        {
            throw new OrionException('Unknown configuration key ['.$key.'].', E_USER_WARNING, $this->CLASS_NAME);
            return null;
        }
        
        return $this->data[$key];
    }

    protected function set($key, $value)
    {
        $this->data[$key] = $value;
    }
}

/**
 * Orion menu entry subclass.
 * This class can be used to create a menu entry in the configuration files.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class OrionMenuEntry
{
    public $text;
    public $module;
    public $route;

    /**
     * Creates a new menu entry for the configuration file
     * @param string $_text Link text
     * @param string $_module Module url wih the extension (ex: home.o)
     * @param string $_route Extra routing (ex: /page/2)
     */
    public function  __construct($_text, $_module, $_route='')
    {
        $this->text = $_text;
        $this->module = $_module;
        $this->route = $_route;
    }
}
?>