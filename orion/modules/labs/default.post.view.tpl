{block name=title}{$title}{/block}

{block name=body}
{nocache}
{if $info neq ""}
<div class="flash {$type}">
    {$info}
</div>
{/if}
{/nocache}
{if $post->title neq ""}
<h1 class="content-title">Showing one post from {$post->category->name}</h1>
<div class="post-list">
    <div class="post">
        <div class="post-box">
            <a class="button-top" title="Back to previous page" href="javascript:history.go(-1);">Go back</a>
            <a class="button-fb" target="_blank" title="Share on Facebook" href="http://www.facebook.com/sharer.php?u={$orion.baseurl}g.o/l/{$post->short}">Facebook</a>
            <a class="button-twit" target="_blank" title="Share on Twitter" href="http://twitter.com/intent/tweet?status={$orion.baseurl}g.o/l/{$post->short}">Twitter</a>
            <a class="button-coms" title="View comments" href="#comments">Comments</a>
        </div>
        <h2 class="post-title"><a href="{$orion.module.url}/post/{$post->category->url}/{$post->url}">{$post->title}</a></h2>
        <div class="post-info">By <a title="About me" href="{$orion.baseurl}about.o">Thibaut Despoulain</a>, posted {$post->date|date_format} in <a title="Filter this category" href="{$orion.module.url}/post/{$post->category->url}">{$post->category->name}</a></div>
        <div class="post-tags">
            {foreach $post->getTagList() as $tag}
            <a class="tag" href="{$orion.module.url}/tag/{$tag}">{$tag}</a>
            {/foreach}
            <div class="clearfix-left"> </div>
        </div>
        <div class="post-intro">{$post->getFormattedIntro()}</div>
        <div class="post-content">{$post->getformattedContent()}</div>
        <div class="clearfix"> </div>
        <div class="post-comments">
            <a name="comments"></a>
            {$disqus_message}
            <div id="disqus_thread"></div>
        </div>
    </div>
</div>
{else}
<h1 class="content-title">Error while retreiving post</h1>
<div class="post-list">
    <div class="post">
        <div class="flash error">
            Post may not exist.
        </div>
    </div>
</div>
{/if}
{include file='inc.side.tpl' cache_id="$side_cache_id" compile_id="$side_cache_id"}
{/block}