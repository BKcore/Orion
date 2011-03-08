<?php
require_once('smarty/Smarty.class.php');
/**
 * Orion SmartyRenderer class.
 * Creates a standard Smarty instance
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class SmartyRenderer extends Smarty
{
    /**
     * Create a standard Smarty template with default caching set to 1 hour.
     */
    public function  __construct()
    {
        parent::__construct();

        $this->template_dir = OrionContext::$PATH.Orion::RENDERER_PATH.'smarty/templates/';
        $this->compile_dir  = OrionContext::$PATH.Orion::RENDERER_PATH.'smarty/templates_c/';
        $this->config_dir   = OrionContext::$PATH.Orion::RENDERER_PATH.'smarty/configs/';
        $this->cache_dir    = OrionContext::$PATH.Orion::RENDERER_PATH.'smarty/cache/';

        $this->caching = Smarty::CACHING_LIFETIME_CURRENT;
        $this->cache_lifetime = 3600;

        $this->enableSecurity();
        
        $this->compile_check = false;
        $this->debugging = true;
    }
}
/*
class SmartySecurity extends Smarty_Security {
  // disable all PHP functions
  public $php_functions = null;
  // remove PHP tags
  public $php_handling = Smarty::PHP_REMOVE;
  //allow template dir
  public function __construct($args)
  {
      parent::__construct($args);
      $this->secure_dir[] = Orion::config()->get('TEMPLATE_PATH');
      $this->secure_dir[] = OrionContext::getModulePath();
  }
}*/
?>
