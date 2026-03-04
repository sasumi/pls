<?php
namespace LFPhp\Pls;
use Exception;
use LFPhp\Logger\Logger;
use function LFPhp\Func\readline;

return [
	'Generate database ORM file',
	function(){
		pls_init_file('tag-orm');
		try{
			$config = pls_get_all_database_config();
			if(!pls_console_confirm('Start to generate ORM files?')){
				return;
			}
			$source_ids = array_keys($config);
			$source_id = '';
			if(count($source_ids) > 1){
				$specs = readline('You have more than one database config, specify which db to generate, default for all DB');
				if($specs){
					$source_id = $specs;
				}
			}
			$source_id = $source_id ?: $source_ids[0];
			$cmd = "php ./script/orm/generate.php --source_id=$source_id";
			Logger::info('Start execute command: '.$cmd);
			echo shell_exec($cmd)."\n";
		}catch(Exception $e){
			Logger::exception($e);
		}
	}
];
