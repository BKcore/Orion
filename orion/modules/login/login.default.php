<?php
/**
 * Orion login module.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
namespace Orion\Modules\Login;
 
use \Orion\Core;
use \Orion\Models;

class LoginDefault extends Core\Controller\Template
{
    protected $name = "login";
    protected $renderer = Core\Renderer::SMARTY;
    protected $template = 'orion-login';

    public function  __construct()
    {
        $this->route = new Core\Route();
        //$this->route->addRule('install', 'install');
        $this->route->addRule('error/?', 'error');
        $this->route->addRule('logout', 'logout');
        $this->route->addRule('do', 'login');
        $this->route->addRule('login', 'index');
        $this->route->addRule('index', 'index');
    }

    public function _index()
    {
        if(Core\Auth::logged())
            Core\Context::redirect(Core\Context::genURL(\Orion::config()->get('DEFAULT_LOGGED_PAGE')));
        $this->assign('title', 'Login');
        $this->assign('target', Core\Context::genModuleURL('login', 'do'));
        $this->renderView('views/login');
    }

    public function _logout()
    {
        Core\Auth::logout();
        $this->assign('type', 'info');
        $this->assign('info', 'Successfully logged out.');
        $this->renderView('views/login');
    }

    public function _login()
    {
        try
        {
            Core\Auth::login();
            if(isset($_SESSION['orion_auth_target']) && $_SESSION['orion_auth_target'] != Core\Context::genModuleURL($this->name))
            {
                $target = $_SESSION['orion_auth_target'];
                unset($_SESSION['orion_auth_target']);
                Core\Context::redirect($target);
            }
            else
                Core\Context::redirect(Core\Context::genURL(\Orion::config()->get('DEFAULT_LOGGED_PAGE')));
        }
        catch (Core\Exception $e)
        {
            $this->assign('info', $e->getMessage());
            $this->assign('type', 'error');
        }
        $this->renderView('views/login');
    }

    /**
     * Quick administrator account creation
     * Run mysite.com/login/install.html once then comment this route/method.
     *
    public function _install()
    {
        $admin = new Models\Auth\User();
        $admin->login = 'login';
        $admin->password = 'p4s5w0r7';
        $admin->name = 'User Name';
        $admin->level = 1;
        $admin->encrypt()->save();
        $this->respond('Administrator account created.');
    }*/
    
    public function _error($e)
    {
        if($e == Core\Route::E_NORULE)
            $msg = 'No rule match found.';
        elseif($e == Core\Auth::E_LOGIN_MISMATCH)
            $msg = 'Error, login mismatch.';
        elseif($e == Core\Auth::E_PASSWORD_MISMATCH)
            $msg = 'Error, password mismatch.';
        else
            $msg = '';

        $this->assign('info', $msg);
        $this->assign('type', 'error');
        $this->renderView('views/login');
    }
}
?>
