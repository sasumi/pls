<?php

use function LFPhp\Func\h;
use function LFPhp\Func\print_exception;
use function LFPhp\Func\text_to_html;
use function LFPhp\PLite\get_app_env;
use const LFPhp\PLite\ENV_PRODUCTION;

// 5xx 错误页面，尽量避免调用其他业务的代码，造成错误循环
// 所以页面模板也尽量不调用 header 之类的外部模板
/** @var \Exception $exception */
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
	      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Error Happens</title>
	<style>
		.page-error{padding: 1em 2em;}
		.page-error h2{font-size:30px;}
		.page-error h2:after{content:""; display:block; border-top:1px solid #dddddd; border-bottom:1px solid white; margin:.5em 0}
	</style>
</head>
<body>
<div class="page-error page-5xx">
	<h2 class="page-cap"><?=h($exception->getMessage());?></h2>
	<?php if(get_app_env() !== ENV_PRODUCTION): ?>
	<p class="desc">
		<?php
		echo text_to_html(print_exception($exception, false, true));
		?>
	</p>
	<?php endif;?>
</div>
</body>
</html>
