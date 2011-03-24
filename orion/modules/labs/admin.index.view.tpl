{block name=title}{$title}{/block}

{block name=body}
{nocache}
    <div class="flash {$type}">
        {$info}
    </div>
	
    <h2>{$subtitle}</h2>
	
    <div class="datatable">
		<div class="dataheader">Latest posts</div>
		<table>
			<tr><th>Actions</th><th>Id</th><th>Url</th><th>Title</th></tr>
			{foreach $posts as $post}
			<tr>
				<td>
					<a href="{$orion.module.uri}/post/edit/{$post->id}" class="action edit">Edit</a>
					<a href="{$orion.module.uri}/post/delete/{$post->id}" class="action delete">Delete</a>
				</td>
				<td>{$post->id}</td>
				<td>{$post->url}</td>
				<td>{$post->title}</td>
			{foreachelse}
			<tr>
				<td colspan="4">No post found</td>
			</tr>
			{/foreach}
		</table>
		<div class="datalinks">
			<a href="{$orion.module.uri}/post/new">New post</a>
			<a href="{$orion.module.uri}/post/list">View more</a>
		</div>
	</div>
	
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
		<div class="datalinks">
			<a href="{$orion.module.uri}/category/new">New category</a>
			<a href="{$orion.module.uri}/category/list">View more</a>
		</div>
	</div>
{/nocache}
{/block}