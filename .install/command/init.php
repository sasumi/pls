<?php
namespace LFPhp\Pls;
use LFPhp\Logger\Logger;
use function LFPhp\Func\console_color;

return [
	'Initialize whole project step by step',
	function(){
		$commands = pls_get_all_commands();
		unset($commands['init']);

		$step_counter = 1;
		$total_command = count($commands);
		Logger::info('==== Start Init Project ====');
		Logger::info("Total $total_command steps to execute.");
		foreach($commands as $step_cmd=>$item){
			Logger::info(" > $step_counter. $step_cmd");
			$step_counter++;
		}

		$step_counter = 1;
		foreach($commands as $cmd=>$item){
			Logger::info('');
			Logger::info(console_color("Step[$step_counter/$total_command] $cmd", 'green'));
			$class = $item['class'];
			$ins = new $class();
			$ins->run();
			$step_counter++;
		}
		Logger::info('---- Project Init Done ----');
	}
];
