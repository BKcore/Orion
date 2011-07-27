<?php
/**
 * Orion admin index module.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class HomeModule extends OrionModule
{
    protected $name = "home";
    protected $renderer = OrionRenderer::SMARTY;

    public function  __construct()
    {
        $this->allow('administrator');

        $this->route = new OrionRoute();
        $this->route->addRule('/index', 'index');
    }

    public function _index()
    {
        $this->assign('title', 'Home');
        $this->flash('info', "Welcome ".OrionAuth::user()->name." !");
        $this->assign('subtitle', 'Please select a module to manage.');
        $this->renderView('admin.index');
    }

    protected function flash($type, $info)
    {
        $this->assign('flash', array('type'=>$type,'info'=>$info));
    }

    public function _error($e)
    {
        if($e == OrionRoute::E_NORULE)
            $msg = 'No rule match found.';
        else
            $msg = '';

        $this->flash('error', $err);
        $this->renderView('admin.index');
    }
}
?>
