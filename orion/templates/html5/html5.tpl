<!doctype html>
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	
	<title>This is a RESTful sample using Orion</title>
	<meta name="description" content="RESTful appication sample written using the Orion PHP Framework.">
	<meta name="author" content="Thibaut Despoulain">
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link rel="stylesheet" href="{$orion.template.abspath}css/style.css?v=2">
    {nocache}{$template.css}{/nocache}
	<script src="{$orion.template.abspath}js/libs/modernizr-1.7.min.js"></script>
</head>
<body>
{block name="flash"}{/block}
	<div id="header-container">
		<header class="wrapper">
			<h1 id="title">RESTful API v0.1</h1>
			<nav>
				{block name="nav"}{/block}
			</nav>
		</header>
	</div>
	<div id="main" class="wrapper">
        {block name=body}{/block}
        <!--
		<aside>
			<h3>Something aside</h3>
		</aside>
		<article>
			<header>
				<h2>Your article heading</h2>
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam sodales urna non odio egestas tempor. Nunc vel vehicula ante. Etiam bibendum iaculis libero, eget molestie nisl pharetra in. In semper consequat est, eu porta velit mollis nec. Curabitur posuere enim eget turpis feugiat tempor. Etiam ullamcorper lorem dapibus velit suscipit ultrices. Proin in est sed erat facilisis pharetra. Pellentesque auctor neque quis nisl lacinia id rutrum lacus venenatis.</p>
			</header>
			<h3>A smaller heading</h3>
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam sodales urna non odio egestas tempor. Nunc vel vehicula ante. Etiam bibendum iaculis libero, eget molestie nisl pharetra in. In semper consequat est, eu porta velit mollis nec. Curabitur posuere enim eget turpis feugiat tempor. Etiam ullamcorper lorem dapibus velit suscipit ultrices. Proin in est sed erat facilisis pharetra. Pellentesque auctor neque quis nisl lacinia id rutrum lacus venenatis.</p>	
			<h3>A smaller heading</h3>
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam sodales urna non odio egestas tempor. Nunc vel vehicula ante. Etiam bibendum iaculis libero, eget molestie nisl pharetra in. In semper consequat est, eu porta velit mollis nec. Curabitur posuere enim eget turpis feugiat tempor. Etiam ullamcorper lorem dapibus velit suscipit ultrices. Proin in est sed erat facilisis pharetra. Pellentesque auctor neque quis nisl lacinia id rutrum lacus venenatis.</p>
			<footer>
			<h3>About the author</h3>
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam sodales urna non odio egestas tempor. Nunc vel vehicula ante. Etiam bibendum iaculis libero, eget molestie nisl pharetra in. In semper consequat est, eu porta velit mollis nec. Curabitur posuere enim eget turpis feugiat tempor.</p>
			</footer>	
		</article>
        -->
	</div>
	<div id="footer-container">
		<footer class="wrapper">
			{block name="footer"}{/block}
		</footer>
	</div>

	{nocache}{$template.js}{/nocache}
	<!--[if lt IE 7 ]>
	<script src="{$orion.template.abspath}js/libs/dd_belatedpng.js"></script>
	<script> DD_belatedPNG.fix('img, .png_bg');</script>
	<![endif]-->
</body>
</html>