{block name=title}{$title}{/block}

{block name=body}
{nocache}
    <div class="flash {$type}">
        {$info}
    </div>
    <h2>{$subtitle}</h2>
    <div class="form">{$form}</div>
{/nocache}
{/block}