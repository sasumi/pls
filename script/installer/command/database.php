<?php
namespace LFPhp\Pls;
use LFPhp\Logger\Logger;
use function LFPhp\Func\readline;

return [
	'Initialize database configuration',
	function(){
		pls_init_file('tag-database');
		$project_info = pls_get_project_info();
		$app_name_var = $project_info['app_name_var'];
		$database_file = pls_get_database_config_file();
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
		if(!pls_console_confirm('Setup database config now?')){
			return;
		}
		$host = pls_console_read_required('Enter database [host]: ', true);
		$database = pls_console_read_required('Enter which [database] to use: ', true);

		$user = readline('Enter the [user] for database: ');
		$user = trim($user);
		$password = readline('Enter the [password] for database: ');

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
		pls_write_php_config_file($database_file, $config);
		Logger::info('Database config saved: '.$database_file);
	},
];
