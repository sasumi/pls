<?php

namespace LFPhp\Pls\command;

use LFPhp\Logger\Logger;
use LFPhp\Pls\ProjectBuilder;
use function LFPhp\Func\dump;
use function LFPhp\Func\readline;
use function LFPhp\Pls\console_confirm;

class CmdOrm extends Cmd {

	public function getCmd(){
		return 'orm';
	}

	public function getDescription(){
		return 'Generate database ORM file';
	}

	public static function getAllModels(){
		$models = [];
		$project_info = ProjectBuilder::getProjectInfo();
		$model_path = "./src/Business/{$project_info['app_name_var']}/Model";
		$files = glob($model_path.'/*.php');
		foreach($files as $f){
			$base_name = basename($f);
			$models[] = substr($base_name, 0, strpos($base_name, '.'));
		}
		if(!$models){
			throw new \Exception('No models found in dir:'.$model_path);
		}
		return $models;
	}

	public function run(){
		ProjectBuilder::initFile('tag-orm');
		try{
			$config = CmdDatabase::getAllDatabaseConfig();
			if(!console_confirm('Start to generate ORM files?')){
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
			echo shell_exec($cmd);
		}catch(\Exception $e){
			Logger::exception($e);
		}
	}
}
