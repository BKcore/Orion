<?php
/**
 * jQuery Fancybox plugin class.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class jQuerySyntaxHighlighterPlugin
{
    const CLASS_NAME = 'jQuerySyntaxHighlighterPlugin';

    const SH_DIR = 'http://alexgorbatchev.com/pub/sh/current/';

    const SH_THEME = 'http://alexgorbatchev.com/pub/sh/current/styles/shThemeDefault.css';

    /**
     * Loads a new SyntaxHighlighter plugin from Alex Gorbatchev's public CDN
     * No args required
     * @param mixed $args
     */
    public static function load()
    {
        try{
            $theme = Orion::config()->defined('SYNTAXHIGHLIGHTER_THEME') ? Orion::config()->get('SYNTAXHIGHLIGHTER_THEME') : self::SH_THEME;

            jQueryPlugin::loadPlugin(self::SH_DIR . 'scripts/shCore.js', true);
            jQueryPlugin::loadPlugin(self::SH_DIR . 'scripts/shAutoloader.js', true);
            jQueryPlugin::loadCSS($theme, true);
            jQueryPlugin::script("
            function path()
            {
              var args = arguments,
                  result = []
                  ;
                   
              for(var i = 0; i < args.length; i++)
                  result.push(args[i].replace('@', '".self::SH_DIR."scripts/'));
                   
              return result
            };
             
            SyntaxHighlighter.defaults['toolbar'] = false;

            SyntaxHighlighter.autoloader.apply(null, path(
              'applescript            @shBrushAppleScript.js',
              'actionscript3 as3      @shBrushAS3.js',
              'bash shell             @shBrushBash.js',
              'coldfusion cf          @shBrushColdFusion.js',
              'cpp c                  @shBrushCpp.js',
              'c# c-sharp csharp      @shBrushCSharp.js',
              'css                    @shBrushCss.js',
              'delphi pascal          @shBrushDelphi.js',
              'diff patch pas         @shBrushDiff.js',
              'erl erlang             @shBrushErlang.js',
              'groovy                 @shBrushGroovy.js',
              'java                   @shBrushJava.js',
              'jfx javafx             @shBrushJavaFX.js',
              'js jscript javascript  @shBrushJScript.js',
              'perl pl                @shBrushPerl.js',
              'php                    @shBrushPhp.js',
              'text plain             @shBrushPlain.js',
              'py python              @shBrushPython.js',
              'ruby rails ror rb      @shBrushRuby.js',
              'sass scss              @shBrushSass.js',
              'scala                  @shBrushScala.js',
              'sql                    @shBrushSql.js',
              'vb vbnet               @shBrushVb.js',
              'xml xhtml xslt html    @shBrushXml.js'
            ));
            SyntaxHighlighter.all();", true);
        }
        catch(OrionException $e)
        {
            throw $e;
        }
    }
}