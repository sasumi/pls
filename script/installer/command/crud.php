<?php
namespace LFPhp\Pls;
use LFPhp\Logger\Logger;

return [
	'Generate CRUD(Create, Read, Update, Delete) operation function',
	function(){
		pls_init_file('tag-crud');
		try{
			pls_get_all_database_config();
			if(!pls_console_confirm('Start to generate CRUD files?')){
				return;
			}
			$models = pls_get_all_models();
			$project_info = pls_get_project_info();
			$cmd = "php ./script/crud/generate.php --source_id={$project_info['app_name_var']} --models=".join(',', $models);
			Logger::info('Start execute command: '.$cmd);
			echo shell_exec($cmd)."\n";
		}catch(\Exception $e){
			Logger::exception($e);
		}
	},
];
