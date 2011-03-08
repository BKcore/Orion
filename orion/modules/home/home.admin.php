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
        $this->assign('info', "Welcome ".OrionAuth::user()->name." !");
        $this->assign('type', 'info');
        $this->displayView('home');
    }

    public function _error($e)
    {
        if($e == OrionRoute::E_NORULE)
            $msg = 'No rule match found.';
        else
            $msg = '';

        $this->assign('info', $msg);
        $this->assign('type', 'error');
        $this->displayView('home');
    }
}
?>
