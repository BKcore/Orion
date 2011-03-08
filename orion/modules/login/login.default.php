<?php
/**
 * Orion login module.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class LoginModule extends OrionModule
{
    protected $name = "login";
    protected $renderer = OrionRenderer::SMARTY;
    protected $template = 'orion-login';

    public function  __construct()
    {
        $this->route = new OrionRoute();
        $this->route->addRule('/error/?', 'error');
        $this->route->addRule('/logout', 'logout');
        $this->route->addRule('/do', 'login');
        $this->route->addRule('/index', 'index');
    }

    public function _index()
    {
        if(OrionAuth::logged())
            OrionContext::redirect(OrionContext::genURL(Orion::config()->get('DEFAULT_LOGGED_PAGE')));
        $this->assign('title', 'Login');
        $this->displayView('login');
    }

    public function _logout()
    {
        OrionAuth::logout();
        $this->assign('type', 'info');
        $this->assign('info', 'Successfully logged out.');
        $this->displayView('login');
    }

    public function _login()
    {
        try
        {
            OrionAuth::login();
            if(isset($_SESSION['orion_auth_target']))
            {
                $target = $_SESSION['orion_auth_target'];
                unset($_SESSION['orion_auth_target']);
                OrionContext::redirect($_SESSION['orion_auth_target']);
            }
            else
                OrionContext::redirect(OrionContext::genURL(Orion::config()->get('DEFAULT_LOGGED_PAGE')));
        }
        catch (OrionException $e)
        {
            $this->assign('info', $e->getMessage());
            $this->assign('type', 'error');
        }
        $this->displayView('login');
    }

    public function _error($e)
    {
        if($e == OrionRoute::E_NORULE)
            $msg = 'No rule match found.';
        elseif($e == OrionAuth::E_LOGIN_MISMATCH)
            $msg = 'Error, login mismatch.';
        elseif($e == OrionAuth::E_PASSWORD_MISMATCH)
            $msg = 'Error, password mismatch.';
        else
            $msg = '';

        $this->assign('info', $msg);
        $this->assign('type', 'error');
        $this->displayView('login');
    }
}
?>
