{block name=title}{$title}{/block}

{block name=body}
{nocache}
{if $info neq ""}
<div class="flash {$type}">
    {$info}
</div>
{/if}
<h1 class="content-title">{$subtitle}</h1>
<div class="post-list">
    {foreach $posts as $post}
    <div class="post">
        <div class="post-box">
            <a rel="fancybox" title="Share on Facebook" href="http://www.facebook.com/sharer.php?u={$orion.baseurl}g.o/l/{$post->short}">Facebook</a>
            <a rel="fancybox" title="Share on Twitter" href="http://twitter.com/intent/tweet?status={$orion.baseurl}g.o/l/{$post->short}">Twitter</a>
            <a title="View comments" href="{$orion.module.url}/post/{$post->category->url}/{$post->url}#comments">Comments</a>
        </div>
        <h2 class="post-title"><a href="{$orion.module.url}/post/{$post->category->url}/{$post->url}">{$post->title}</a></h2>
        <div class="post-info">By <a title="About me" href="{$orion.baseurl}about.o">Thibaut Despoulain</a>, posted {$post->date|date_format} in <a title="Filter this category" href="{$orion.module.url}/post/{$post->category->url}">{$post->category->name}</a></div>
        <div class="post-tags">
            {foreach $post->getTagList() as $tag}
            <a class="tag" href="{$orion.module.url}/tag/{$tag}">{$tag}</a>
            {/foreach}
            <div class="clearfix"> </div>
        </div>
        <div class="post-intro">{$post->getFormattedIntro()}</div>
        {if $post@iteration eq 1}
        <div class="post-content">{$post->getformattedContent()}</div>
        <a class="post-readmore" href="{$orion.module.url}/post/{$post->category->url}/{$post->url}#comments">View comments</a>
        {else}
            {if $post->content neq ""}
            <a class="post-readmore" href="{$orion.module.url}/post/{$post->category->url}/{$post->url}">Read more</a>
            {else}
            <a class="post-readmore" href="{$orion.module.url}/post/{$post->category->url}/{$post->url}#comments">View comments</a>
            {/if}
        {/if}
        <div class="clearfix"> </div>
    </div>
    {foreachelse}
    <div class="post-message">
        No post to display.
    </div>
    {/foreach}
    <div class="post-nav">
        {if $prevOffset neq ""}
            <a class="previous" href="{$orion.module.url}/offset/{$prevOffset}">Newer posts</a>
        {/if}
        {if $nextOffset neq ""}
            <a class="next" href="{$orion.module.url}/offset/{$nextOffset}">Older posts</a>
        {/if}
    </div>
</div>
{/nocache}
{include file='inc.side.tpl' cache_id="$side_cache_id" compile_id="$side_cache_id"}
{/block}