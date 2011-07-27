<?php
/**
 * Orion admin settings module.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class SettingsModule extends OrionModule
{
    protected $name = "settings";
    protected $renderer = OrionRenderer::SMARTY;

    public function  __construct()
    {
        $this->allow('administrator');

        $this->route = new OrionRoute();
        $this->route->addRule('/do/?', 'do');
        $this->route->addRule('/do', 'do');
        $this->route->addRule('/index', 'index');
    }
    
    public function _do($what=null)
    {
        if($what == 'clearcache')
        {
            $this->tpl->clearAllCache();
            $this->flash('info', 'Smarty cache directory cleared.');
        }
        elseif($what == 'clearcompiled')
        {
            $this->tpl->clearCompiledTemplate();
            $this->flash('info', 'Smarty compiled directory cleared.');
        }
        
        $this->_index();
    }

    public function _index()
    {
        $this->assign('title', 'Orion settings');
        $links = array(new OrionMenuEntry("Clear Smarty cache directory.", OrionContext::getModuleURI().'/do/clearcache')
                      ,new OrionMenuEntry("Clear Smarty compiled directory.", OrionContext::getModuleURI().'/do/clearcompiled')
                      );
        $this->assign('links', $links);
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
