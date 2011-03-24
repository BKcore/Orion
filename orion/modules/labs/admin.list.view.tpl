{block name=title}{$title}{/block}

{block name=body}
{nocache}
	<div class="flash {$type}">
		{$info}
	</div>

{if $posts neq ""}
	<div class="datatable">
		<div class="dataheader">{$subtitle}</div>
		<table>
			<tr><th>Actions</th><th>Id</th><th>date</th><th>category</th><th>Title</th></tr>
			{foreach $posts as $post}
			<tr>
				<td>
					<a href="{$orion.module.uri}/post/edit/{$post->id}" class="action edit">Edit</a>
					<a href="{$orion.module.uri}/post/delete/{$post->id}" class="action delete">Delete</a>
				</td>
				<td>{$post->id}</td>
				<td>{$post->date|date_format}</td>
				<td><a href="{$orion.module.uri}/category/edit/{$post->category->id}">{$post->category->name}</a></td>
				<td>{$post->title}</td>
			{foreachelse}
			<tr>
				<td colspan="5">No post found</td>
			</tr>
			{/foreach}
		</table>
		<div class="datanav">
		{if $prevOffset neq ""}
			<a class="previous" href="{$orion.module.uri}/post/list/offset/{$prevOffset}">Newer posts</a>
		{/if}
		{if $nextOffset neq ""}
			<a class="next" href="{$orion.module.uri}/post/list/offset/{$nextOffset}">Older posts</a>
		{/if}
		</div>
		<div class="datalinks">
			<a href="{$orion.module.uri}/post/new">New post</a>
		</div>
	</div>
{/if}

{if $cats neq ""}
	<div class="datatable">
		<div class="dataheader">Latest categories</div>
		<table>
			<tr><th>Actions</th><th>Id</th><th>Url</th><th>Name</th></tr>
			{foreach $cats as $cat}
			<tr>
				<td>
					<a href="{$orion.module.uri}/category/edit/{$cat->id}" class="action edit">Edit</a>
					<a href="{$orion.module.uri}/category/delete/{$cat->id}" class="action delete">Delete</a>
				</td>
				<td>{$cat->id}</td>
				<td>{$cat->url}</td>
				<td>{$cat->name}</td>
			{foreachelse}
			<tr>
				<td colspan="4">No category found</td>
			</tr>
			{/foreach}
		</table>
		<div class="datanav">
		{if $prevOffset neq ""}
			<a class="previous" href="{$orion.module.uri}/category/list/offset/{$prevOffset}">Newer posts</a>
		{/if}
		{if $nextOffset neq ""}
			<a class="next" href="{$orion.module.uri}/category/list/offset/{$nextOffset}">Older posts</a>
		{/if}
		</div>
		<div class="datalinks">
			<a href="{$orion.module.uri}/category/new">New category</a>
		</div>
	</div>
{/if}
{/nocache}
{/block}