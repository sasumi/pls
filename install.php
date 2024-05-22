#!/usr/bin/env php

<?php

use function LFPhp\Func\console_color;

include_once __DIR__.'/vendor/autoload.php';

$bath_file = "pls.bat";
$bat_content = "
@ECHO OFF
php %~dp0/vendor/lfphp/pls/run.php %*
";
file_put_contents($bath_file, $bat_content);

echo PHP_EOL.console_color(' PLS Installer ', 'white', 'yellow'),PHP_EOL,
"Congratulation!! PLS(Plite Scaffold) installed success.",PHP_EOL,
"Now you can run ./pls.bat to maintains your project.",PHP_EOL,PHP_EOL;
