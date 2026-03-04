<?php
namespace LFPhp\Pls;

use LFPhp\Logger\Logger;
use function LFPhp\Func\console_color;

return [
	'Initialize whole project step by step',
	function(){
		$all_steps = [
			'reset',
			'base',
			'database',
			'orm',
			'crud',
			'front',
		];
		$all_commands = pls_get_all_commands();
		$commands = [];
		foreach($all_steps as $step){
			$commands[$step] = $all_commands[$step];
		}

		$step_counter = 1;
		$total_command = count($commands);
		echo '==== Start Init Project ====', PHP_EOL;
		echo "Total $total_command steps to execute.", PHP_EOL;
		foreach($commands as $step_cmd => $item){
			echo "[$step_counter] $step_cmd", PHP_EOL;
			$step_counter++;
		}

		$step_counter = 1;
		foreach($commands as $cmd => $_){
			echo PHP_EOL, console_color("[$step_counter/$total_command] $cmd", 'green'), PHP_EOL;
			pls_run_cmd($cmd);
			$step_counter++;
		}
		Logger::info('---- Project Initialized Done ----');
	},
];
