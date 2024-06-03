<?php

namespace LFPhp\Pls\command;

use function LFPhp\Func\console_color;

class CmdHelp extends Cmd {

	public function getCmd(){
		return 'help';
	}

	public function getDescription(){
		return 'show this help';
	}

	public function run(){
		$commands = self::getAllCmd();
		$cmd_str = [];
		foreach($commands as $cmd => $item){
			$cmd_str[] = console_color(str_pad($cmd, 13, ' ', STR_PAD_RIGHT), 'green')." ".console_color($item['description'], 'light_gray');
		}
		$helper_msg = console_color('Usage:', 'brown')."\n".
			"composer pls <command> [...arguments]\n\n".
			console_color('Available commands:', 'brown')."\n".join("\n", $cmd_str)."\n";
		echo $helper_msg;
	}
}
