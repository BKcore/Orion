<?php
/**
 * Orion Renderer manager class.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class OrionRenderer
{
    const CLASS_NAME = 'OrionRenderer';

    /**
     * Template renderers flags
     */
    const SMARTY = 'smarty';
    const OXML = 'oxml';
    const OJSON = 'ojson';

    const DEFAULT_RENDERER = 'smarty';

    /**
     * Current renderer's instance
     * @var TemplateRenderer
     */
    public static $CURRENT=null;

    /**
     * Set the current renderer to $template (Smarty by default)
     * @param string $template Template renderer's name
     * @return TemplateRenderer (A smarty instance by default)
     */
    public static function setRenderer($renderer)
    {
        $file = OrionContext::$PATH.Orion::RENDERER_PATH.$renderer.Orion::RENDERER_EXT.'.php';
        $class = ucfirst($renderer).Orion::RENDERER_SUFFIX;

        if(!file_exists($file))
            throw new OrionException('Renderer class does not exist.', E_USER_ERROR, self::CLASS_NAME);

        require_once($file);
        self::$CURRENT = new $class();

        return self::$CURRENT;
    }
}
?>
