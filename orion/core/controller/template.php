<?php

/**
 * Orion Template Controller class.
 *
 * Extend this class to create a new controller.
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 *
 * @author Thibaut Despoulain
 * @version 0.11.12
 */

namespace Orion\Core\Controller;

use \Orion\Core;

abstract class Template extends Core\Controller
{

    /**
     * Template renderer (OrionTemplate::SMARTY by default). Can be overriden in the module
     *
     * @var string
     */
    protected $renderer = Core\Renderer::DEFAULT_RENDERER;

    /**
     * Template renderer instance
     * @var object
     */
    protected $tpl = null;

    /**
     * Base template name, used as a parent template for local views.
     * Set this to null if view have no parent template.
     * @var string
     */
    protected $template = null;

    /**
     * Main module function, executed right after module loading by Orion.
     * Handles route parsing and function callbacks.
     */
    public function load()
    {
        if ( $this->tpl == null )
            $this->setRenderer( $this->renderer );

        if ( $this->template == null )
            $this->setTemplate( Core\Context::getDefaultTemplate() );
        else
            $this->setTemplate( $this->template );

        parent::load();
    }

    /**
     * assigns a Smarty variable.
     *
     * @param array|string $block the template variable name(s)
     * @param mixed $value the value to assign
     * @param boolean $nocache if true any output of this variable will be not cached
     */
    protected function assign( $block, $content, $nocache=false )
    {
        if ( $this->tpl == null )
            $this->setRenderer( $this->renderer );
        $this->tpl->assign( $block, $content, $nocache );
    }

    /**
     * Clears cache of given view
     * @param string $view
     * @param string $id 
     */
    protected function clearCache( $view, $id=null )
    {
        try
        {
            $filename = Core\Context::$PATH . \Orion::MODULE_PATH . $this->name . DIRECTORY_SEPARATOR . $view . '.tpl';

            $this->tpl->clearCache( $filename, $id );
        }
        catch ( Core\Exception $e )
        {
            throw $e;
        }
        catch ( \Exception $e )
        {
            throw new Core\Exception( $e->getMessage(), E_USER_WARNING, $this->name );
        }
    }

    /**
     * Shortcuts the includeCSS method of the renderer, using a relative file path.
     * @param string $file The CSS file path, relative to the module directory
     */
    protected function includeCSS( $file )
    {
        $filename = Core\Context::getModuleAbsolutePath() . $file;
        $this->tpl->includeCSS( $filename );
    }

    /**
     * Displays template (shortlink for $this->tpl->display(...)).
     * @deprecated Use displayView() and local views instead
     * @param string $template the resource handle of the template file  or template object
     * @param mixed $id cache id to be used with this template
     */
    protected function render( $template, $id=null, $compile_id=null )
    {
        $this->tpl->render( $template, $id, $compile_id );
    }

    /**
     * Displays view (shortlink for $this->tpl->display('file:'.$local_view_file)).
     *
     * @param string $view The name of the view file to display, without any extension or path. For example, to load local index.view.tpl, use $this->displayView('index').
     * @param mixed $cache_id cache id to be used with this template
     * @param mixed $compile_id compile id to be used with this template
     * @param object $parent next higher level of Smarty variables
     */
    protected function renderView( $view, $id=null, $compile_id=null )
    {
        try
        {
            $filename = Core\Context::$PATH . \Orion::MODULE_PATH . $this->name . DS . $view . '.tpl';

            if ( !file_exists( $filename ) )
                throw new Core\Exception( 'View file [' . $filename . '] does not exist.', E_USER_WARNING, $this->name );

            $this->tpl->renderView( $filename, $this->template, $id, $compile_id );
        }
        catch ( Core\Exception $e )
        {
            throw $e;
        }
        catch ( \Exception $e )
        {
            throw new Core\Exception( $e->getMessage(), E_USER_WARNING, $this->name );
        }
    }

    /**
     * Tests if given view is cached
     * @param string $view
     * @param string $cache_id
     * @param string $template_id
     * @return boolean 
     */
    protected function isCached( $view, $cache_id=null, $template_id=null )
    {
        try
        {
            $filename = Core\Context::$PATH . \Orion::MODULE_PATH . $this->name . DS . $view . '.tpl';

            if ( !file_exists( $filename ) )
                throw new Core\Exception( 'View file [' . $filename . '] does not exist.', E_USER_WARNING, $this->name );

            return $this->tpl->isViewCached( $filename, $this->template, $cache_id, $template_id );
        }
        catch ( Core\Exception $e )
        {
            throw $e;
        }
        catch ( \Exception $e )
        {
            throw new Core\Exception( $e->getMessage(), E_USER_WARNING, $this->name );
        }
    }

    /**
     * Get current template name
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Override current renderer and reset template variable
     * @param string $renderer
     */
    protected function setRenderer( $renderer )
    {
        $this->renderer = $renderer;
        $this->tpl = Core\Renderer::setRenderer( $renderer );
        if ( $this->template != null )
            $this->tpl->addTemplateDir( Core\Context::getTemplatePath( $this->template ) );
        $this->tpl->addTemplateDir( Core\Context::getModulePath() );
    }

    /**
     * Set a new template theme.
     * @param string $name 
     */
    public function setTemplate( $name )
    {
        if ( $name == $this->template )
            return false;

        if ( !file_exists( Core\Context::getTemplateFilePath( $name ) ) )
            throw new Core\Exception( 'Template not found in [' . Core\Context::getTemplateFilePath( $name ) . ']', E_USER_WARNING, $this->name );

        $this->template = $name;

        if ( $this->tpl != null )
            $this->tpl->addTemplateDir( Core\Context::getTemplatePath( $this->template ) );
    }

}

?>
