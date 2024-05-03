<?php

use function LFPhp\Func\get_all_opt;
use function LFPhp\Func\get_screen_size;
use function LFPhp\Func\mkdir_batch;
use function LFPhp\Pls\get_pls_config;

include dirname(__DIR__).'/vendor/autoload.php';

const ARG_REQUIRED = 1;
const ARG_OPTIONAL = 2;

$PLS_DOC = <<<EOT
Plite project scaffold.
command pattern: php {$_SERVER['SCRIPT_NAME']} [command] [...params] 
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

switch($command){
	case 'init':
		$project_dir = PLS_ROOT.'/test/prj1';
		$file_struct = get_pls_config('file/file_structure');
		$file_struct = str_replace('$PROJECT_DIR', $project_dir, $file_struct);
		mkdir_batch($file_struct);
		echo 'Project file structure created:',PHP_EOL;
		foreach($file_struct as $f){
			echo realpath($f), PHP_EOL;
		}
		echo "success";
		exit;
}


