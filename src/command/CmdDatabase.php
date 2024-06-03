<?php

namespace LFPhp\Pls\command;

use LFPhp\Logger\Logger;
use LFPhp\Pls\ProjectBuilder;
use function LFPhp\Func\readline;
use function LFPhp\Pls\console_confirm;
use function LFPhp\Pls\console_read_required;
use function LFPhp\Pls\write_php_config_file;

class CmdDatabase extends Cmd {

	public function getCmd(){
		return 'database';
	}

	public function getDescription(){
		return 'Initialize database configuration';
	}

	public function run(){
		ProjectBuilder::initFile('tag-database');

		$project_info = ProjectBuilder::getProjectInfo();
		$app_name_var = $project_info['app_name_var'];
		$app_root = $project_info['app_root'];
		$database_file = $app_root.'/config/database.inc.php';
		$config = [];
		if(is_file($database_file)){
			try{
				$config = include $database_file;
				Logger::debug('Database config file already exists: '.$database_file, $config);
				if(isset($config[$app_name_var])){
					Logger::debug('Database config item already exists: ', $config[$app_name_var]);
					return;
				}
			}catch(\Exception $e){
				Logger::error('Database config file no readable: '.$e->getMessage());
				Logger::exception($e);
				return;
			}
		}
		if(!console_confirm('Setup database config now?')){
			return;
		}
		$host = console_read_required('Enter database host: ', true);
		$database = console_read_required('Enter which database to use: ', true);

		$user = readline('Enter the user for database: ');
		$user = trim($user);
		$password = readline('Enter the password for database: ');

		if(!is_file($database_file)){
			Logger::info('Create database config file: '.$database_file);
			touch($database_file);
		}

		$config[$app_name_var] = [
			'host'     => $host,
			'user'     => $user,
			'password' => $password,
			'database' => $database,
		];
		write_php_config_file($database_file, $config);
		Logger::info('Database config saved: '.$database_file);
	}
}
