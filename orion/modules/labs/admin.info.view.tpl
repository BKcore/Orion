{block name=title}{$title}{/block}

{block name=body}
{nocache}
<h2>{$subtitle}</h2>

<div class="flash {$type}">
    {$info}
</div>

<div class="links">
{if $links neq ""}
    <ul>
    {foreach $links as $link}
        <li><a href="{$orion.baseurl}{$link->module}{$link->route}">{$link->text}</a></li>
    {/foreach}
    </ul>
{/if}
</div>
{/nocache}
{/block}