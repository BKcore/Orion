<?php
/**
 * Orion index module.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.11.10
 */
namespace Orion\Modules\Index;
 
use \Orion\Core;

class IndexDefault extends Core\Controller\Template
{
    protected $name = "index";
    protected $renderer = Core\Renderer::SMARTY;

    public function  __construct()
    {
        $this->route = new Core\Route();
        $this->route->addRule('index', 'index');
    }

    public function _index()
    {
        $this->renderView('views/index', 'home');
    }
}
?>
