<div class="side-col">
    <div class="side-block">
        <h1 class="side-title">Categories</h1>
            {foreach $categories as $category}
            <a class="cat" href="{$orion.module.url}/post/{$category->url}">{$category->name}</a>
            {/foreach}
    </div>
    <div class="side-block">
        <h1 class="side-title">Tags</h1>
        <div class="side-tags">
            {foreach $tags as $side_tag}
            <a class="tag" href="{$orion.module.url}/tag/{$side_tag->name}">{$side_tag->name}</a>
            {/foreach}
        </div>
    </div>
</div>