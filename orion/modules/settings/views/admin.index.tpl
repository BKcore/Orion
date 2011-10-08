{block name=title}{$title}{/block}

{block name=body}
<h2>What do you want to do ?</h2>
<div class="links">
{if $links neq ""}
    <ul>
    {foreach $links as $link}
        <li><a href="{$link->getURL()}">{$link->text}</a></li>
    {/foreach}
    </ul>
{/if}
</div>
{/block}