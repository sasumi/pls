#!/usr/bin/env php

<?php

use function LFPhp\Func\console_color;
use function LFPhp\Func\get_all_opt;
use function LFPhp\Pls\console_confirm;

include_once __DIR__.'/vendor/autoload.php';

$args = get_all_opt();
array_shift($args);
$target = trim($args[0]);

if(!$target || $target === 'help'){
	echo PHP_EOL.console_color(' PLS Installer ', 'white', 'yellow').PHP_EOL.
		console_color('Usage:', 'brown'), PHP_EOL.
		'php install.php', PHP_EOL,PHP_EOL.
		console_color('Example:', 'brown'), PHP_EOL.
		'php install.php /www/projectA';
	exit;
}

$target_dir = $_SERVER['PWD'];

$tools_root = $target_dir.'/vendor/lfphp/pls/';

$bath_file = "pls.bat";
$bat_content = "
@ECHO OFF
php %~dp0/vendor/lfphp/pls/run.php %*
";
file_put_contents($bath_file, $bat_content);

echo "Script Installed Success: $bath_file ",PHP_EOL,
"Now you can run [$bath_file] to maintains PLITE Project";
