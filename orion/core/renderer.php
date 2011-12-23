<?php

namespace Orion\Core;


/**
 * \Orion\Core\Renderer
 * 
 * Orion Renderer manager class.
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 *
 * @author Thibaut Despoulain
 * @version 0.11.12
 *
 * @static
 */
class Renderer
{
    /**
     * Template renderers flags
     */
    const SMARTY = 'smarty';

    const DEFAULT_RENDERER = 'smarty';

    /**
     * Current renderer's instance
     * @var TemplateRenderer
     */
    public static $CURRENT = null;

    /**
     * Set the current renderer to $template (Smarty by default)
     * @param string $template Template renderer's name
     * @return TemplateRenderer (A smarty instance by default)
     */
    public static function setRenderer( $renderer )
    {
        $file = Context::$PATH . \Orion::RENDERER_PATH . $renderer . '.php';
        $class = '\Orion\\Renderers\\' . ucfirst( $renderer );

        if ( !file_exists( $file ) )
            throw new Exception( 'Renderer class does not exist.', E_USER_ERROR, self::CLASS_NAME );

        require_once($file);
        self::$CURRENT = new $class();

        return self::$CURRENT;
    }

}

?>
