<?php

namespace LFPhp\Pls\command;

use LFPhp\Logger\Logger;
use LFPhp\Pls\ProjectBuilder;
use function LFPhp\Func\readline;
use function LFPhp\Pls\console_confirm;

class CmdCrud extends Cmd {

	public function getCmd(){
		return 'crud';
	}

	public function getDescription(){
		return 'Generate CRUD(Create, Read, Update, Delete) operation function';
	}

	public function run(){
		ProjectBuilder::initFile('tag-crud');
		try{
			CmdDatabase::getAllDatabaseConfig();
			if(!console_confirm('Start to generate CRUD files?')){
				return;
			}
			$models = CmdOrm::getAllModels();
			$project_info = ProjectBuilder::getProjectInfo();
			$cmd = "php ./script/crud/generate.php --source_id={$project_info['app_name_var']} --models=".join(',', $models);
			Logger::info('Start execute command: '.$cmd);
			echo shell_exec($cmd);
		}catch(\Exception $e){
			Logger::exception($e);
		}
	}
}
