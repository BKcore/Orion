<?php

/**
 * Orion Renderer base interface.
 * 
 * If your template engine does not support one of those methods, you still have to define them. 
 * Just put the default return value inside them for compatibility.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.11.10
 */

namespace Orion\Core\Renderer;

interface Base
{
    public function __construct();
    
    /**
     * assigns a template variable.
     *
     * @param array|string $block the template variable name(s)
     * @param mixed $value the value to assign
     * @param boolean $nocache if true any output of this variable will be not cached
     */
    public function assign($key, $value, $nocache);
    /**
     * Clears a template cache file
     * @param String $file Template path
     * @param String $cache_id The caching identifier (if supported by renderer)
     * @param String $compile_id The compile identifier (if supported by renderer)
     */
    public function clearCache($template, $cache_id);
    /**
     * Checks if a template file is cached
     * @param String $file Template path
     * @param String $cache_id The caching identifier (if supported by renderer)
     * @param String $compile_id The compile identifier (if supported by renderer)
     * @return Boolean Default=FALSE
     */
    public function isCached($template, $cache_id, $compile_id);
    /**
     * Render a single template file
     * @param String $file Template path
     * @param String $cache_id The caching identifier (if supported by renderer)
     * @param String $compile_id The compile identifier (if supported by renderer)
     */
    public function render($file, $cache_id, $compile_id);
    /**
     * Render a view file, extending a master template
     * @param String $file View template path
     * @param String $master Master template path (if supported by renderer)
     * @param String $cache_id The caching identifier (if supported by renderer)
     * @param String $compile_id The compile identifier (if supported by renderer)
     */
    public function renderView($file, $master, $cache_id, $compile_id);
    
    /**
     * Adds raw javascript script to template
     * @param String $rawjs
     */
    public function addJS($rawjs);
    /**
     * Includes a javascript file
     * @param String $jsfile Javascript file path
     */
    public function includeJS($jsfile);
    /**
     * Includes a CSS file
     * @param String $cssfile CSS file path
     */
    public function includeCSS($cssfile);
}

?>
