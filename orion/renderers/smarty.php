<?php
/**
 * \Orion\Renderers\Smarty class.
 * Creates a standard Smarty instance
 *
 * @author Thibaut Despoulain
 * @version 1.0
 */
 
namespace Orion\Renderers;

require_once('smarty/Smarty.class.php');

use \Orion\Core;

class Smarty extends \Smarty implements Core\Renderer\Base
{
    // private $HTML_C_TAG = ''; // set this to '' if HTML output instead of xHTML

    private $js = array();
    private $css = array();
    /**
     * Create a standard Smarty template with default caching set to 1 hour.
     */
    public function  __construct()
    {
        parent::__construct();

        $this->template_dir = \Orion::config()->get('TEMPLATE_PATH');
        $this->compile_dir  = \Orion::base() . \Orion::RENDERER_PATH.'smarty/templates_c/';
        $this->config_dir   = \Orion::base() . \Orion::RENDERER_PATH.'smarty/configs/';
        $this->cache_dir    = \Orion::base() . \Orion::RENDERER_PATH.'smarty/cache/';

        $this->caching = \Smarty::CACHING_LIFETIME_SAVED;
        $this->cache_lifetime = 3600;

        $this->disableSecurity();
        
        $this->compile_check = false;
        $this->debugging = false;
        
        $this->force_compile = false;
    }

    public function addJS($jsd)
    {
        if(!in_array($jsd, $this->js))
           $this->js[] = $jsd;
    }

    public function includeJS($jsfile)
    {
        $jsd = '<script type="text/javascript" src="'.$jsfile.'"></script>';
        if(!in_array($jsd, $this->js))
           $this->js[] = $jsd;
    }

    public function includeCSS($cssfile)
    {
        $cssd = '<link rel="stylesheet" type="text/css" href="'.$cssfile.'" '.$this->HTML_C_TAG.'>';

        if(!in_array($cssd, $this->css))
           $this->css[] = $cssd;
    }
    
    public function isViewCached($file, $extension=null, $id=null, $compile_id=null)
    {
        if($extension == null)
            $output = $file;
        else
            $output = 'extends:'.$extension . \Orion::TEMPLATE_EXT.'|'.$file;

        return $this->isCached($output, $id, $compile_id);
    }

    public function render($file, $id=null, $compile_id=null)
    {
        $template = array("js" => implode("\n", $this->js), "css" => implode("\n", $this->css));
        $this->assign('template', $template);

        $this->assign('orion', $this->getDataArray());
        $this->display($file, $id, $compile_id);
    }

    public function renderView($file, $extension=null, $id=null, $compile_id=null)
    {
        if($extension == null)
            $output = $file;
        else
            $output = 'extends:'.$extension . \Orion::TEMPLATE_EXT.'|'.$file;

        $this->render($output, $id, $compile_id);
    }
    
    /**
     * Get important context data as an array (useful for template hydratation)
     */
    public function getDataArray()
    {
        $array = array( );
        try
        {
            $array[ 'module' ] = array( );
            $array[ 'module' ][ 'name' ] = \Orion::module()->getName();
            $array[ 'module' ][ 'path' ] = \Orion\Core\Context::getModulePath();
            $array[ 'module' ][ 'url' ] = \Orion\Core\Context::getModuleURL( \Orion::module()->getName() );
            $array[ 'module' ][ 'uri' ] = \Orion\Core\Context::getModuleURI();
            $array[ 'module' ][ 'fulluri' ] = \Orion\Core\Context::getFullURI();
            $array[ 'template' ] = array( );
            $array[ 'template' ][ 'name' ] = \Orion::module()->getTemplate();
            $array[ 'template' ][ 'path' ] = \Orion\Core\Context::getTemplatePath( \Orion::module()->getTemplate() );
            $array[ 'template' ][ 'abspath' ] = \Orion\Core\Context::getTemplateAbsolutePath( \Orion::module()->getTemplate() );
            if ( \Orion::config()->defined( strtoupper( \Orion::getMode() ) . '_MENU' ) )
                $array[ 'menu' ] = \Orion::config()->get( strtoupper( \Orion::getMode() ) . '_MENU' );
            $array[ 'title' ] = \Orion::config()->get( 'SITE_NAME' );
            $array[ 'description' ] = \Orion::config()->get( 'SITE_DESC' );
            $array[ 'author' ] = \Orion::config()->get( 'SITE_AUTHOR' );
            $array[ 'baseurl' ] = \Orion::config()->get( 'BASE_URL' );
            $array[ 'mode' ] = \Orion::getMode();
            $array[ 'logged' ] = \Orion\Core\Auth::logged() ? 'yes' : 'no';
            if(\Orion\Core\Auth::user() != null)
            {
                $array[ 'user' ] = array();
                $array[ 'user' ][ 'login' ] = \Orion\Core\Auth::user()->login;
                $array[ 'user' ][ 'hasadmin' ] = \Orion\Core\Auth::user()->is('moderator', true);
            }
        } catch ( Exception $e )
        {
            $array[ 'error' ] = 'Unable to retreive all data.';
        }

        return $array;
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
      $this->secure_dir[] = Core\Context::getModulePath();
  }
}*/
?>
