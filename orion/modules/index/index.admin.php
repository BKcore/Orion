<?php
/**
 * Orion admin index module.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
namespace Orion\Modules\Index;
 
use \Orion\Core;

class IndexAdmin extends Core\Controller\Template
{
    protected $name = "index";
    protected $renderer = Core\Renderer::SMARTY;

    public function  __construct()
    {
        $this->allow('administrator');

        $this->route = new Core\Route();
        $this->route->addRule('index', 'index');
    }

    public function _index()
    {
        $this->assign('title', 'Home');
        $this->flash('info', "Welcome ".Core\Auth::user()->name." !");
        $this->assign('subtitle', 'Please select a module to manage.');
        $this->renderView('views/admin.index');
    }

    protected function flash($type, $info)
    {
        $this->assign('flash', array('type'=>$type,'info'=>$info));
    }

    public function _error($e)
    {
        if($e == Core\Route::E_NORULE)
            $msg = 'No rule match found.';
        else
            $msg = '';

        $this->flash('error', $err);
        $this->renderView('views/admin.index');
    }
}
?>
