{block name=title}{$title}{/block}

{block name=body}

<div class="flash warning">
    {$output}
</div>

<h2>{$subtitle}</h2>

<div class="postlist">
{foreach $posts as $post}
    <div class="post">
        <h3>{$post->title}</h3>
        <div class="infos">{$post->date|date_format} - By <span class="info">Thibaut Despoulain</span> in <a class="info" href="{$orion.module.url}/post/{$post->caturl}">{$post->catname}</a></div>
        <div class="tags">
        {foreach $posts->getTagList() as $tag}
            <a class="tag" href="{$orion.module.url}/tag/{$tag->name}">{$tag->name}</a>
        {/foreach}
        </div>
        <div class="intro">{$post->intro}</div>
    </div>
{foreachelse}
    <div class="flash notice">No entry found.</div>
{/foreach}
</div>

<div class="pagenav">
{if $prevOffset >= 0}
    <a class="previous" href="{$orion.module.url}/{$modeurl}page/{$prevOffset}">Newer posts</a>
{/if}
{if $nextOffset > 0}
    <a class="next" href="{$orion.module.url}/{$modeurl}page/{$nextOffset}">Older posts</a>
{/if}
</div>

{/block}