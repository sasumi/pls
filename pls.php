<?php
namespace LFPhp\Pls;

use Composer\InstalledVersions;
use LFPhp\Logger\Logger;
use LFPhp\Logger\LoggerLevel;
use LFPhp\Logger\Output\ConsoleOutput;
use LFPhp\Logger\Output\FileOutput;
use function LFPhp\Func\console_color;
use function LFPhp\Func\get_all_opt;

/**
 * Plite Framework Starter
 */
include_once __DIR__.'/vendor/autoload.php';
include_once __DIR__.'/script/installer/autoload.php';

Logger::registerGlobal(function($messages, $level, $logger_id, $trace_info){
	[$fore_color, $bg_color] = ConsoleOutput::$COLOR_MAP[$level] ?: [];
	echo console_color(Logger::combineMessages($messages), $fore_color, $bg_color).PHP_EOL;
}, LoggerLevel::DEBUG);
Logger::registerGlobal(new FileOutput(PLS_PROJECT_ROOT.'/.runtime/install.log', LoggerLevel::INFO));

$args = get_all_opt();
Logger::debug('Arguments detected: ', json_encode($args));
define('DRY_RUN', $args['dry-run']);

$cmd = array_shift($args);
$all_commands = pls_get_all_commands();
echo "\n".str_repeat('=', 40)."\n".console_color(' PLite Project Builder ', 'white', 'yellow')."\n";
echo "Version: ".InstalledVersions::getVersion('lfphp/pls')."\n";
echo "Command example: php ".basename(__FILE__).' COMMAND [arguments]', PHP_EOL;
echo str_repeat("-", 40)."\n";

if(!$all_commands[$cmd]){
	pls_run_cmd('help');
}else{
	pls_run_cmd($cmd);
}
