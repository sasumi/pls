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

	public static function getAllDatabaseConfig(){
		$database_file = self::getDatabaseConfigFile();
		if(!$database_file){
			throw new \Exception('No database config file:'.$database_file);
		}
		$config = include $database_file;
		if(!$config || !is_array($config)){
			throw new \Exception('Database config empty: '.$database_file);
		}
		return $config;
	}

	public static function getDatabaseConfigFile(){
		$project_info = ProjectBuilder::getProjectInfo();
		$app_root = $project_info['app_root'];
		$f = $app_root.'/config/database.inc.php';
		return is_file($f) ? $f : null;
	}

	public function run(){
		ProjectBuilder::initFile('tag-database');

		$project_info = ProjectBuilder::getProjectInfo();
		$app_name_var = $project_info['app_name_var'];
		$database_file = self::getDatabaseConfigFile();
		$config = [];
		if($database_file){
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
