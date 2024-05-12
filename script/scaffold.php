<?php

use function LFPhp\Func\get_all_opt;
use function LFPhp\Func\get_screen_size;

include dirname(__DIR__).'/vendor/autoload.php';

const ARG_REQUIRED = 1;
const ARG_OPTIONAL = 2;

$PLS_DOC = <<<EOT
Plite project scaffold.
command pattern: php {$_SERVER['SCRIPT_NAME']} [command] [...params] 
global parameters:
 -h show help
 -v show verbose
 -y confirm operation as default [yes]
EOT;

const SUPPORT_COMMAND_DOC = [
	'init' => [
		'Initialize plite project',
		'--namespace' => 'Setup project namespace, use composer project name as default.',
	],
	'list' => [
		'List all support command',
	],
	'orm'  => [
		'ORM operation for DB Models and DB Tables',
		'update'  => 'Update all ORM model and table object from database configs, regenerate all table model but skip exist models.',
		'--table' => 'Specify tables, use comma [,] to separate more than two tables.',
	],
	'crud' => [
		'generate CRUD file',
		'--table' => '',
	],
	'',
];

//start logic
$options = get_all_opt();
$script_name = array_shift($options);
$command = array_shift($options);
list($screen_size_width) = get_screen_size() ?: [80];

if(!SUPPORT_COMMAND_DOC[$command]){
	echo PHP_EOL, $PLS_DOC, PHP_EOL;
	echo str_repeat('-', $screen_size_width - 1), PHP_EOL;
	echo 'SUPPORT COMMANDS:', PHP_EOL;
	foreach(SUPPORT_COMMAND_DOC as $command => $p){
		echo $command, "\t\t", ($p[0] ?: ''), PHP_EOL;
	}
	exit;
}

$arg_str = join(' ', $options);
$CMD_SCRIPT = realpath(__DIR__.'/'.$command.'.php');
$call = "php $CMD_SCRIPT $arg_str";

echo "Exec Command: ", $command, PHP_EOL;
$ret = shell_exec($call);
echo $ret;
