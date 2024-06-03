<?php

use function LFPhp\Func\h;
use function \LFPhp\Func\html_tag_css;
use function LFPhp\PLite\get_config;
use function LFPhp\PLite\url;

?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
	      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>{app_name}</title>
	<?=html_tag_css('css/style.css');?>
</head>
<body>
	<h1>{app_name}</h1>
	<nav>
		<?php
			$navs = get_config('nav');
			foreach($navs as $uri=>$title):?>
		<li>
			<a href="<?=url($uri);?>"><?=h($title);?></a>
		</li>
		<?php endforeach;?>
	</nav>
