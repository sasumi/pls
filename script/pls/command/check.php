<?php
namespace LFPhp\Pls;
use LFPhp\Logger\Logger;

return [
	'confirm project environment variables',
	function(){
		$envs = pls_get_project_info();
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
		if(!pls_console_confirm("Is these information correct?")){
			die('Exit');
		}
	},
];
