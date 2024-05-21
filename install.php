#!/usr/bin/env php

<?php

use function LFPhp\Func\console_color;
use function LFPhp\Func\get_all_opt;
use function LFPhp\Pls\console_confirm;

include_once __DIR__.'/vendor/autoload.php';

$args = get_all_opt();
array_shift($args);
$target = trim($args[0]);

\LFPhp\Func\dump($args);

if(!$target || $target === 'help'){
	echo PHP_EOL.console_color(' PLS Installer ', 'white', 'yellow').PHP_EOL.
		console_color('Usage:', 'brown'), PHP_EOL.
		'php install.php <target>', PHP_EOL,PHP_EOL.
		console_color('Example:', 'brown'), PHP_EOL.
		'php install.php /www/projectA';
	exit;
}

\LFPhp\Func\dump($_SERVER, 1);

if($target[0] !== '/'){
	\LFPhp\Func\dump($target, 1);
	$target = $_SERVER['PWD'].$target;
}

if(!realpath($target)){
	echo console_color('install path no exists:'.$target, 'red');
	exit;
}
\LFPhp\Func\dump($target);
$target = realpath($target);

if(!console_confirm('PLS will install to ['.console_color($target, 'brown'))){
	exit;
}


\LFPhp\Func\dump($_SERVER['PWD'], 1);
