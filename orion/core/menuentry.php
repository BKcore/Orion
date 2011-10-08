<?php
/**
 * Orion MenuEntry class.
 * Ease menu and links creation and storage
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
namespace Orion\Core;

class MenuEntry
{
    public $text=null;
    public $module=null;
    public $route=null;
    public $mode=null;

    /**
     * Creates a new menu entry for the configuration file
     * @param string $_text Link text
     * @param string $_module Module url wih the extension (ex: home.o)
     * @param string $_route Extra routing (ex: /page/2)
     */
    public function  __construct($_text, $_module, $_route=null, $_mode=null)
    {
        $this->text = $_text;
        $this->module = $_module;
        $this->route = $_route;
        $this->mode = $_mode;
    }
    
    /**
     * Generates and returns corresponding URL
     * @return string
     */
    public function getURL()
    {
        return Context::genModuleURL($this->module, $this->route, $this->mode);
    }
}
?>
