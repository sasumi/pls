<?php

namespace LFPhp\Pls\command;

use LFPhp\Logger\Logger;
use LFPhp\Pls\ProjectBuilder;
use function LFPhp\Func\is_assoc_array;
use function LFPhp\Func\readline;
use function LFPhp\Func\run_command;
use function LFPhp\Pls\console_confirm;

class CmdOrm extends Cmd {

	public function getCmd(){
		return 'orm';
	}

	public function getDescription(){
		return 'Generate database ORM file';
	}

	public function run(){
		ProjectBuilder::initFile('tag-orm');
		$project_info = ProjectBuilder::getProjectInfo();
		$app_name_var = $project_info['app_name_var'];
		$app_root = $project_info['app_root'];
		$database_file = $app_root.'/config/database.inc.php';
		if(!is_file($database_file)){
			Logger::warning('No database config file detected: '.$database_file);
			return;
		}
		try{
			$config = include $database_file;
			if(empty($config)){
				Logger::warning('Empty database config in file: '.$database_file, $config);
				return;
			}
			if(!is_assoc_array($config) || count($config) == count($config, COUNT_RECURSIVE)){
				Logger::error('Wrong config format in file, two dimension in assoc array required. '.$database_file);
				return;
			}

			if(!console_confirm('Start to generate ORM files?')){
				return;
			}

			//todo
			$source_ids = array_keys($config);
			$source_id = '';
			if(count($source_ids) > 1){
				$specs = readline('You have more than one database config, specify which db to generate, default for all DB');
				if($specs){
					$source_id = $specs;
				}
			}
			$source_id = $source_id ?: $source_ids[0];
			$cmd = 'php '.realpath($app_root.'/script/orm/generate.php')." --source_id=$source_id";
			Logger::info('Start execute command: '.$cmd);
			run_command($cmd, [], true);
			return;
		}catch(\Exception $e){
			LOgger::error('Error while reading database config file: '.$database_file);
			Logger::exception($e);
		}
	}
}
