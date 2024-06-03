<?php

namespace LFPhp\Pls\command;
use LFPhp\Logger\Logger;
use LFPhp\Pls\ProjectBuilder;
use function LFPhp\Pls\console_confirm;

class CmdEnvConfirm extends Cmd {

	public function getCmd(){
		return 'env';
	}

	public function getDescription(){
		return 'confirm project environment variables';
	}

	public function run(){
		$envs = ProjectBuilder::getProjectInfo();
		$envs_str = [];
		foreach($envs as $k => $v){
			$ks = ucwords(str_replace(['-', '_'], [' ', ' '], $k));
			$ks = ucfirst($ks);
			$envs_str[] = "$ks: \t$v";
		}
		$envs_str = join(PHP_EOL, $envs_str);
		$info = <<<EOT

------------------------------
$envs_str
------------------------------ 
EOT;
		Logger::info($info);
		if(!console_confirm("Is these information correct?")){
			die('Exit');
		}
	}
}
