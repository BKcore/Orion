<?php
/**
 * Orion module base class.
 * Extend this class to create a new module.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
abstract class OrionModule
{
    const CLASS_NAME = 'OrionModule';
    const FUNCTION_PREFIX = '_';
    
    /**
     * Module name placeholder, used for base routing.
     * Module name must be lowercase.
     * If module name is "module" then you can access it via BASEURL/module/.
     * @example A GuestbookModule module name has to be guestbook
     * @var string
     */
    protected $name = null;

    /**
     * Template renderer (OrionTemplate::SMARTY by default). Can be overriden in the module
     *
     * @var string
     */
    protected $renderer = OrionRenderer::DEFAULT_RENDERER;

    /**
     * Module route object placeholder.
     * Must be created in child module consturctor.
     *
     * @var OrionRoute
     */
    protected $route = null;

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
     * Resticted functions names
     */
    private $RESTRICTED_FUNCTIONS = array('__construct'
                                      ,'__destruct'
                                      ,'toString'
                                      ,'load'
                                      ,'isRestrictedFunction'
                                      ,'setView');
    
    private $FUNCTION_NAME_MATCH = '[a-zA-Z_]+';

    /**
     * Main module function, executed right after module loading by Orion.
     * Handles route parsing and function callbacks.
     */
    public function load()
    {
        if($this->tpl == null) $this->setRenderer($this->renderer);

        if($this->template == null)
            $this->setTemplate(OrionContext::getDefaultTemplate());
        else
            $this->setTemplate($this->template);

        if($this->route == null)
            throw new OrionException('No route object found in module.', E_USER_ERROR, self::CLASS_NAME);

        $function = $this->route->decode();

        if(OrionTools::startWith($function->getName(), '__'))
            throw new OrionException('Trying to access a resticted function.', E_USER_ERROR, self::CLASS_NAME);

        if(OrionTools::startWith($function->getName(), self::FUNCTION_PREFIX))
            throw new OrionException('Function name in rule must be declared without function prefix '.self::FUNCTION_PREFIX.'.', E_USER_ERROR, self::CLASS_NAME);

        if(!is_callable(array($this, self::FUNCTION_PREFIX.$function->getName())))
            OrionContext::redirect(404);

        OrionTools::callClassMethod($this, self::FUNCTION_PREFIX.$function->getName(), $function->getArgs());
    }

    /**
     * Allows access only to logged users that have a level equal to or less than provided role. If permission is not granted, it will automatically redirect the user to the login module.
     * <p><b>Note that while it's doing all login/auth/redirection work automatically, you still have to create the corresponding user table in your database in addition to provide the login module into orion's module directory.</b></p>
     * @see OrionAuth
     *      MainConfig
     *      LoginModule
     * @link http://bkcore.com/labs.o/post/orion/How_to_set_up_user_auth
     * @param string $slug the role identifier (ie: 'administrator', 'member', etc.). See your configuration file for a liste of roles and their permission level.
     */
    public function allow($slug)
    {
        try {
            OrionAuth::login();
            if(!OrionAuth::allow($slug))
            {// this exception prevents any redirection defect or hack
                throw new Exception('Access denied', E_USER_ERROR, $this->name);
            }
        }
        catch(OrionException $e)
        {
            throw $e;
        }
    }

    public function useModel($modelname)
    {
        $filename = OrionContext::$PATH . Orion::MODULE_PATH . $this->name . '/' . $modelname. Orion::MODEL_EXT . '.php';

        if(!file_exists($filename))
            throw new OrionException('['.$modelname.'] Model file not found in module directory.', E_USER_WARNING, $this->name);
    
        require_once($filename);
    }

    /**
     * Override current renderer and reset template variable
     */
    protected function setRenderer($renderer)
    {
        $this->renderer = $renderer;
        $this->tpl = OrionRenderer::setRenderer($renderer);
        if($this->template != null)
            $this->tpl->addTemplateDir(OrionContext::getTemplatePath($this->template));
        $this->tpl->addTemplateDir(OrionContext::getModulePath());
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
            $filename = OrionContext::$PATH . Orion::MODULE_PATH . $this->name . DIRECTORY_SEPARATOR . $view . Orion::VIEW_EXT . '.tpl';

            if(!file_exists($filename))
                throw new OrionException('View file ['.$filename.'] does not exist.', E_USER_WARNING, $this->name);

            $this->tpl->renderView($filename, $this->template, $id, $compile_id);
        }
        catch(OrionException $e)
        {
            throw $e;
        }
        catch(Exception $e)
        {
            throw new OrionException($e->getMessage(), E_USER_WARNING, $this->name);
        }
    }
    
    protected function clearCache($view, $id=null)
    {
        try {
            $filename = OrionContext::$PATH . Orion::MODULE_PATH . $this->name . DIRECTORY_SEPARATOR . $view . Orion::VIEW_EXT . '.tpl';

            $this->tpl->clearCache($filename, $id);
        }
        catch(OrionException $e)
        {
            throw $e;
        }
        catch(Exception $e)
        {
            throw new OrionException($e->getMessage(), E_USER_WARNING, $this->name);
        }
    }

    protected function isCached($file, $cache_id=null, $template_id=null)
    {
        return $this->tpl->isCached($file, $cache_id, $template_id);
    }

    /**
     * Security function name testing.
     *
     * @param string Function name to test
     *
     * @see OrionSecurity
     */
    private function isRestrictedFunction($name)
    {
        return (!OrionTools::startWith($function, '_')
                && OrionTools::match($function, $this->FUNCTION_NAME_MATCH)
                && in_array($function, $this->RESTRICTED_FUNCTIONS));
    }

    /**
     * Returns module name identifier
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($name)
    {
        if($name == $this->template)
            return false;

        if(!file_exists(OrionContext::getTemplateFilePath($name)))
            throw new OrionException('Template not found in ['.OrionContext::getTemplateFilePath($name).']', E_USER_WARNING, $this->name);

        $this->template = $name;
        
        if($this->tpl != null)
            $this->tpl->addTemplateDir(OrionContext::getTemplatePath($this->template));
    }
}
?>
