<?php
/**
 * Orion config class.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.11.10
 */
namespace Orion\Configs;

use \Orion\Core;

class Main extends Core\Config
{    
    public function load()
    {
        // Base site name (Appears as html page name for example)
        $this->set('SITE_NAME', 'Orion, the PHP5 framework.');
        // Base site description
        $this->set('SITE_DESC', 'Orion is an open-source, lightweight yet powerful web framework written in PHP5.');
        // Author
        $this->set('SITE_AUTHOR', 'Author');
        
        // SQL connection data
        $this->set('DB_TYPE', 'sql');
        $this->set('SQL_DRIVER', 'mysql');
        $this->set('SQL_HOST', 'host');
        $this->set('SQL_DBNAME', 'dbname');
        $this->set('SQL_USER', 'user');
        $this->set('SQL_PASSWORD', 'pass');
        
        // Roles for authentification purpose
        $this->set('AUTH_ROLES', array('administrator' => 1
                                      ,'staff'         => 10
                                      ,'moderator'     => 50
                                      ,'user'          => 100));

        // User DB table for authentification
        $this->set('AUTH_TABLE_USER', 'orion_auth_users');
        
        // Auth/Login module name
        $this->set('AUTH_MODULE', 'login');
        
        // Security key used for salting and hashing
        $this->set('SECURITY_KEY', '10ch4r5k3y');

        // Base url of the project
        $this->set('BASE_URL', 'http://yourwebsite.com/anydir/');
        
        // Path from root URL, used for URI parsing ('/' if no sub directory)
        $this->set('BASE_DIR', '/anydir/');
        
        // regex that delimits the module slug form its URI.
        $this->set('MODULE_SEPARATOR', '/');

        // Default 404 error document URL
        $this->set('URL_404', array('default' => 'http://127.0.0.1/orion/404.html',
                                    'admin' => 'http://127.0.0.1/orion/404.html',
                                    'json' => 'http://127.0.0.1:8080/neta/404.json'));

        // Base upload folder (needs rw+ access)
        $this->set('UPLOAD_DIR', 'orion/uploads/');
        
        // Folder containing images uploads (needs rw+ access)
        $this->set('IMAGE_UPLOAD_DIR', 'images/');

        // Folder containing files uploads (needs rw+ access)
        $this->set('FILE_UPLOAD_DIR', 'files/');

        // Standard template folder path (for php)
        $this->set('TEMPLATE_PATH', 'orion/templates/');
        
        // Absolute template folder path (for css and tpl variables), prevents url rewrite issues.
        $this->set('TEMPLATE_ABS_PATH', 'http://yourwebsite.com/anydir/orion/templates/');

        // Open modules list (security)
        // $this->set('OPEN_MODULES', array('index', 'login', 'settings'));

        // Default module (when no module uri is provided, aka root module)
        $this->set('DEFAULT_MODULE', 'index');

        // Default page to redirect to when user logs in
        $this->set('DEFAULT_LOGGED_PAGE', 'index.admin');

        // Lists of usable modes
        $this->set('MODE_LIST', array('default'  => '.html'
                                     ,'admin'   => '.admin'
                                     ,'json'    => '.json'));

        // Default mode
        $this->set('DEFAULT_MODE', 'default');

        // Modes default templates
        $this->set('DEFAULT_TEMPLATE', 'html5');
        $this->set('ADMIN_TEMPLATE', 'orion-admin');

        // Modes default menus
        $this->set('DEFAULT_MENU', array(new Core\MenuEntry('Home', 'index')
                                        ,new Core\MenuEntry('Login', 'login')));

        // Modes default menus
        $this->set('ADMIN_MENU', array(new Core\MenuEntry('Home page', 'index')
                                      ,new Core\MenuEntry('Settings', 'settings')));
    }
}
?>
