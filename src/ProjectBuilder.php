<?php

namespace LFPhp\Pls;

use LFPhp\Logger\Logger;
use function LFPhp\PLite\get_app_name;
use function LFPhp\PLite\get_app_namespace;

class ProjectBuilder {
	public static function init(){
		$steps = [
			'Project Configuration Confirm'  => [ProjectBuilder::class, 'projectEnvConfirm'],
			'Initialize project directories' => [ProjectBuilder::class, 'MakeDir'],
			'Initialize project base file'   => [ProjectBuilder::class, 'initFile'],
			'Configuration Database'         => [ProjectBuilder::class, 'initDatabase'],

		];
		Logger::warning('==== Start Init Project ==== ');

		$total_steps = count($steps);
		$step_index = 0;
		foreach($steps as $name => $payload){
			Logger::info("Step [$step_index/$total_steps]: ", $name);
			call_user_func($payload);
		}
		Logger::info('---- Project Init Done ----');
	}

	private static function projectEnvConfirm(){
		$app_name = get_app_name();
		$namespace = get_app_namespace();
		$info = <<<EOT
Project name detected: $app_name
Project namespace to be used: $namespace 
EOT;
		Logger::info($info);
	}

	/**
	 * 初始化必要的文件
	 * @return void
	 */
	private static function initFile(){
	}

	/**
	 * 创建文件夹目录
	 * @return void
	 * @throws \Exception
	 */
	private static function MakeDir(){
		$file_structs = get_pls_config('file/file_structure');
		foreach($file_structs as $dir){
			if(!is_dir($dir)){
				if(!mkdir($dir, 0x777, true)){
					throw new \Exception('Make directory fail:'.$dir);
				}
				Logger::info('Directory created:', $dir);
			}else{
				Logger::warning('Directory already exists.', $dir);
			}
		}
	}

	/**
	 * 初始化数据库配置
	 * @return void
	 */
	private static function initDatabase(){
	}
}
