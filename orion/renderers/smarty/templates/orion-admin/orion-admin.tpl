<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{block name=title}Index{/block} | Orion administration</title>
<link type="text/css" rel="stylesheet" href="{$orion.template.abspath}css/main.style.css" />
	{$template.css}
</head>

<body>
	<div id="wrapper" class="hsprite">
		<div id="header"><h1 class='title'>Orion administration</h1></div>
        <div id="main">
				<div class="vmenu col">
					<div class="logo">Orion</div>
					<ul>
                    {foreach $orion.menu as $item}
                        {if $item->module eq $orion.module.uri}
                            <li><a class="active" href="{$orion.baseurl}{$item->module}{$item->route}">{$item->text}</a></li>
                            {if $submenu neq ""}
                                <ul class="submenu">
                                {foreach $submenu as $mitem}
                                    <li><a href="{$orion.baseurl}{$mitem->module}{$mitem->route}">{$mitem->text}</a></li>
                                {/foreach}
                                </ul>
                            {/if}
                        {else}
                            <li><a href="{$orion.baseurl}{$item->module}{$item->route}">{$item->text}</a></li>
                        {/if}
                    {/foreach}
					</ul>
				</div>
				<div class="content col">
                {block name=body}{/block}
			</div>
        </div>
    </div>
	{$template.js}
</body>
</html>
