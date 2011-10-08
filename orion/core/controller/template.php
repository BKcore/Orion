<?php
/**
 * Orion Template Controller class.
 *
 * Extend this class to create a new controller.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
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
        if($this->tpl == null) $this->setRenderer($this->renderer);

        if($this->template == null)
            $this->setTemplate(Core\Context::getDefaultTemplate());
        else
            $this->setTemplate($this->template);

        if($this->route == null)
            throw new Core\Exception('No route object found in module.', E_USER_ERROR, get_class($this));

        $function = $this->route->decode();

        if(Core\Tools::startWith($function->getName(), '__'))
            throw new Core\Exception('Trying to access a resticted function.', E_USER_ERROR, get_class($this));

        if(Core\Tools::startWith($function->getName(), self::FUNCTION_PREFIX))
            throw new Core\Exception('Function name in rule must be declared without function prefix '.self::FUNCTION_PREFIX.'.', E_USER_ERROR, get_class($this));

        if(!is_callable(array($this, self::FUNCTION_PREFIX.$function->getName())))
            Core\Context::redirect(404);

        Core\Tools::callClassMethod($this, self::FUNCTION_PREFIX.$function->getName(), $function->getArgs());
    }

    /**
     * assigns a Smarty variable.
     *
     * @param array|string $block the template variable name(s)
     * @param mixed $value the value to assign
     * @param boolean $nocache if true any output of this variable will be not cached
     */
    protected function assign($block, $content, $nocache=false)
    {
        if($this->tpl == null) $this->setRenderer($this->renderer);
        $this->tpl->assign($block, $content, $nocache);
    }
    
    protected function clearCache($view, $id=null)
    {
        try {
            $filename = Core\Context::$PATH . \Orion::MODULE_PATH . $this->name . DIRECTORY_SEPARATOR . $view . \Orion::VIEW_EXT . '.tpl';

            $this->tpl->clearCache($filename, $id);
        }
        catch(Core\Exception $e)
        {
            throw $e;
        }
        catch(\Exception $e)
        {
            throw new Core\Exception($e->getMessage(), E_USER_WARNING, $this->name);
        }
    }
    
    protected function includeCSS($file)
    {
        $filename = Core\Context::getModuleAbsolutePath() . $file;
        $this->tpl->includeCSS($filename);
    }

    /**
     * Displays template (shortlink for $this->tpl->display(...)).
     * @deprecated Use displayView() and local views instead
     * @param string $template the resource handle of the template file  or template object
     * @param mixed $id cache id to be used with this template
     */
    protected function render($template, $id=null, $compile_id=null)
    {
        $this->tpl->render($template, $id, $compile_id);
    }

    /**
     * Displays view (shortlink for $this->tpl->display('file:'.$local_view_file)).
     *
     * @param string $view The name of the view file to display, without any extension or path. For example, to load local index.view.tpl, use $this->displayView('index').
     * @param mixed $cache_id cache id to be used with this template
     * @param mixed $compile_id compile id to be used with this template
     * @param object $parent next higher level of Smarty variables
     */
    protected function renderView($view, $id=null, $compile_id=null)
    {
        try {
            $filename = Core\Context::$PATH . \Orion::MODULE_PATH . $this->name . DS . $view . '.tpl';

            if(!file_exists($filename))
                throw new Core\Exception('View file ['.$filename.'] does not exist.', E_USER_WARNING, $this->name);

            $this->tpl->renderView($filename, $this->template, $id, $compile_id);
        }
        catch(Core\Exception $e)
        {
            throw $e;
        }
        catch(\Exception $e)
        {
            throw new Core\Exception($e->getMessage(), E_USER_WARNING, $this->name);
        }
    }

    protected function isCached($view, $cache_id=null, $template_id=null)
    {
        try {
            $filename = Core\Context::$PATH . \Orion::MODULE_PATH . $this->name . DS . $view . '.tpl';

            if(!file_exists($filename))
                throw new Core\Exception('View file ['.$filename.'] does not exist.', E_USER_WARNING, $this->name);

            return $this->tpl->isViewCached($filename, $this->template, $cache_id, $template_id);
    }
        catch(Core\Exception $e)
        {
            throw $e;
        }
        catch(\Exception $e)
        {
            throw new Core\Exception($e->getMessage(), E_USER_WARNING, $this->name);
        }
    }

    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Override current renderer and reset template variable
     */
    protected function setRenderer($renderer)
    {
        $this->renderer = $renderer;
        $this->tpl = Core\Renderer::setRenderer($renderer);
        if($this->template != null)
            $this->tpl->addTemplateDir(Core\Context::getTemplatePath($this->template));
        $this->tpl->addTemplateDir(Core\Context::getModulePath());
    }

    public function setTemplate($name)
    {
        if($name == $this->template)
            return false;

        if(!file_exists(Core\Context::getTemplateFilePath($name)))
            throw new Core\Exception('Template not found in ['.Core\Context::getTemplateFilePath($name).']', E_USER_WARNING, $this->name);

        $this->template = $name;
        
        if($this->tpl != null)
            $this->tpl->addTemplateDir(Core\Context::getTemplatePath($this->template));
    }
}
?>
