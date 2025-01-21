<?php
namespace LFPhp\Pls;
use function LFPhp\Func\console_color;

return [
	'show this help',
	function(){
		$commands = pls_get_all_commands();
		$cmd_str = [];
		foreach($commands as $cmd => $item){
			$cmd_str[] = console_color(str_pad($cmd, 13, ' ', STR_PAD_RIGHT), 'green')." ".console_color($item[0], 'light_gray');
		}
		$helper_msg =
			console_color('Global Parameter:', 'brown')."\n".
			console_color("  -w", 'green').console_color(" Override exists files", 'white')."\n ".
			console_color(" -y", 'green').console_color(" auto confirm alert message", 'white')."\n\n".
			console_color('Usage:', 'brown')."\n".
			"composer pls <command> [...arguments]\n\n".
			console_color('Available commands:', 'brown')."\n".join("\n", $cmd_str)."\n";
		echo $helper_msg;
	}
];
