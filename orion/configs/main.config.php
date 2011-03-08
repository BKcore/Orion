<?php
/**
 * Orion config class.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class MainConfig extends OrionConfig
{
    protected $CLASS_NAME = 'MainConfig';
    
    public function load()
    {
        // Base site name (Appears as html page name for example)
        $this->set('SITE_NAME', 'My website');

        // SQL connection data
        $this->set('SQL_DRIVER', 'mysql');
        $this->set('SQL_HOST', 'host');
        $this->set('SQL_DBNAME', 'dbname');
        $this->set('SQL_USER', 'dbuser');
        $this->set('SQL_PASSWORD', 'dbpwd');
        
        // Roles for authentification purpose
        $this->set('AUTH_ROLES', array('administrator'  => 1
                                      ,'member'         => 10
                                      ,'visitor'        => 100));

        // User DB table for authentification
        $this->set('AUTH_TABLE_USER', 'orion_auth_users');

        // Base url of the project
        $this->set('BASE_URL', 'http://mysite.com/mydir/');
        
        // Path from root URL (used for URI parsing)
        $this->set('BASE_DIR', '/mydir/');

        // Default 404 error document URL
        $this->set('URL_404', 'http://mysite.com/404.html');

        // Standard template folder path (for php)
        $this->set('TEMPLATE_PATH', 'orion/renderers/smarty/templates/');
        
        // Absolute template folder path (for css and tpl variables), prevents url rewrite issues.
        $this->set('TEMPLATE_ABS_PATH', 'http://mysite.com/mydir/orion/renderers/smarty/templates/');

        // Open modules list (security)
        $this->set('OPEN_MODULES', array('home', 'login', 'labs'));

        // Default module (when no module uri is provided, aka root module)
        $this->set('DEFAULT_MODULE', 'home');

        // Default page to redirect to when user logs in
        $this->set('DEFAULT_LOGGED_PAGE', 'home.a');

        // Lists of usable modes
        $this->set('MODE_LIST', array('default'  => '.o'
                                     ,'admin'   => '.a'
                                     ,'json'    => '.json'
                                     ,'xml'     => '.xml'));

        // Default mode
        $this->set('DEFAULT_MODE', 'default');

        // Modes default templates
        $this->set('DEFAULT_TEMPLATE', 'orion-admin');
        $this->set('ADMIN_TEMPLATE', 'orion-admin');

        // Modes default menus
        $this->set('DEFAULT_MENU', array('home'       => 'Home'
                                        ,'login'      => 'Login'
										,'labs'		  => 'Labs (WIP)'));

        // Modes default menus
        $this->set('ADMIN_MENU', array('home'         => 'Home'
									  ,'labs'		  => 'Labs (WIP)'));
    }
}
?>
