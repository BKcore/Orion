<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{block name=title}Index{/block} - Orion administration</title>
<link type="text/css" rel="stylesheet" href="{$orion.template.abspath}css/admin.style.css" />
	{$template.css}
</head>

<body>
    <div class="flash {$flash.type nocache}">
        {$flash.info nocache}
    </div>
    <div id="header">
        <div id="header-top">
            <div class="global-container">
                <h1 id="header-logo">
                    Orion administration
                </h1>
                <div id="header-nav">
                    <a href="{$orion.baseurl}login/logout.admin">Log out</a> -
                    <a href="{$orion.baseurl}">View website</a>
                </div>
                <div class="clearfix"> </div>
            </div>
        </div>
        <div id="header-menu">
            <div class="global-container">
                {foreach $orion.menu as $item}
                {if $item@iteration neq 1} &bull; {/if}<a href="{$item->getURL()}" {if $item->module eq $orion.module.uri}class="active"{/if}>{$item->text}</a>
                {/foreach}
            </div>
        </div>
        <div id="header-submenu">
            <div class="global-container">
                {foreach $submenu as $mitem}
                {if $mitem@iteration neq 1} &bull; {/if}<a href="{$mitem->getURL()}">{$mitem->text}</a>
                {/foreach}
            </div>
        </div>
    </div>
    <div id="global">
        <div id="main">
            <div class="global-container"> 
                {block name=body}{/block}
            </div>
        </div>
    </div>
    <div id="footer">
        Orion administration theme by <a href="http://bkcore.com/" target="_blank">Thibaut Despoulain</a> - Proudly powered by <a href="http://orion.bkcore.com/" target="_blank">Orion</a>
    </div>
	{$template.js}
</body>
</html>
