<?php
namespace Orion\Core;

/**
 * \Orion\Core\MenuEntry
 * 
 * Orion MenuEntry class.
 * Ease menu and links creation and storage
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 *
 * @author Thibaut Despoulain
 * @version 0.11.12
 */
class MenuEntry extends Object
{
    /**
     * Link's text
     * @var string
     */
    public $text=null;
    /**
     * Target module's name
     * @var string
     */
    public $module=null;
    /**
     * Target module's sub URI
     * @var string
     */
    public $route=null;
    /**
     * Target module's mode
     * @var string
     */
    public $mode=null;
    /**
     * Link class
     * @var string
     */
    public $class=null;
    /**
     * Link icon class
     * @var string
     */
    public $icon=null;

    /**
     * Creates a new menu entry for the configuration file
     * @param string $_text Link text
     * @param string $_module Module url wih the extension (ex: home.o)
     * @param string $_route Extra routing (ex: /page/2)
     * @param string $_mode The mode
     * @param string $_class The link CSS class
     * @param string $_icon The icon CSS class (without the icon- prefix)
     */
    public function  __construct($_text, $_module, $_route=null, $_mode=null, $_class='', $_icon='none')
    {
        $this->text = $_text;
        $this->module = $_module;
        $this->route = $_route;
        $this->mode = $_mode;
        $this->class = $_class;
        $this->icon = $_icon;
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
