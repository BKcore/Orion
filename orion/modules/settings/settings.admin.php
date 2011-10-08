<?php
/**
 * Orion admin settings module.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
namespace Orion\Modules\Settings;
 
use \Orion\Core;

class SettingsAdmin extends Core\Controller\Template
{
    protected $name = "settings";
    protected $renderer = Core\Renderer::SMARTY;

    public function  __construct()
    {
        $this->allow('administrator');

        $this->route = new Core\Route();
        $this->route->addRule('do/?', 'do');
        $this->route->addRule('do', 'do');
        $this->route->addRule('index', 'index');
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
        $links = array(new Core\MenuEntry("Clear Smarty cache directory.", $this->name, 'do/clearcache')
                      ,new Core\MenuEntry("Clear Smarty compiled directory.", $this->name, 'do/clearcompiled')
                      );
        $this->assign('links', $links);
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
