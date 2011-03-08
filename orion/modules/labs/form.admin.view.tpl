{block name=title}{$title}{/block}

{block name=body}

<div class="flash {$type}">
    {$info}
</div>

<h2>{$subtitle}</h2>

<div class="form">{nocache}{$form}{/nocache}</div>

{/block}