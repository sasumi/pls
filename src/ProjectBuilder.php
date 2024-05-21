<?php

namespace LFPhp\Pls;

use Composer\InstalledVersions;
use LFPhp\Logger\Logger;
use LFPhp\Logger\LoggerLevel;
use LFPhp\Logger\Output\ConsoleOutput;
use function LFPhp\Func\array_get;
use function LFPhp\Func\console_color;
use function LFPhp\Func\get_all_opt;
use function LFPhp\Func\glob_recursive;
use function LFPhp\Func\is_assoc_array;
use function LFPhp\Func\readline;
use function LFPhp\Func\run_command;
use function LFPhp\Func\underscores_to_pascalcase;

include_once dirname(__DIR__).'/vendor/autoload.php';

class ProjectBuilder {
	const TEMPLATE_PROJECT = __DIR__.'/../template';
	private static $app_root;

	private static $commands = [
		'help'          => ['help', 'show this help'],
		'init'          => ['init', 'Initialize whole project step by step'],
		'env'           => ['projectEnvConfirm', 'Shop project environment variables'],
		'init-file'     => ['initFile', 'Initialize project basic directory structure'],
		'init-database' => ['initDatabase', 'Initialize database configuration'],
		'orm'           => ['generateORM', 'Generate database ORM file'],
		'index'         => ['generateIndexPage', 'Generate Index controller & page'],
		'crud'          => ['generateCRUD', 'Generate CRUD(Create, Read, Update, Delete) operation function'],
		'frontend'      => ['integratedFrontend', 'Generate CRUD(Create, Read, Update, Delete) operation function'],
	];

	public static function start(){
		Logger::registerGlobal(new ConsoleOutput(), LoggerLevel::DEBUG);

		$args = get_all_opt();
		Logger::debug('Arguments detected: ', $args);
		$command = array_shift($args);
		$support_commands = array_map('strtolower', array_keys(self::$commands));
		if(!in_array($command, $support_commands)){
			self::help();
		}else{
			self::showTitle();
			call_user_func([self::class, self::$commands[$command][0]]);
		}
	}

	private static function showTitle(){
		echo "\n".console_color(' PLite Project Builder ', 'white', 'yellow')."\n";
		echo "Version: ".InstalledVersions::getVersion('lfphp/pls')."\n";
	}

	public static function help(){
		$commands = [];
		foreach(self::$commands as $cmd => list($method, $title)){
			$commands[] = console_color($cmd, 'green')." \t\t".console_color($title, 'light_gray');
		}
		$helper_msg = self::showTitle().console_color('Usage:', 'brown')."\n"."composer pls <command> [...arguments]\n\n".console_color('Available commands:', 'brown')."\n".join("\n", $commands)."\n";
		echo $helper_msg;
	}

	public static function init(){
		$steps = [
			'env',
			'init-file',
			'init-database',
			'orm',
			'index',
			'crud',
			'frontend',
		];
		$step_counter = 1;
		$total_command = count($steps);
		Logger::info('==== Start Init Project ====');
		Logger::info("Total $total_command steps to execute.");
		foreach($steps as $step_cmd){
			$title = self::$commands[$step_cmd][1];
			Logger::info(" > $step_counter. $title");
			$step_counter++;
		}

		$step_counter = 1;
		foreach($steps as $step_cmd){
			$title = self::$commands[$step_cmd][1];
			Logger::info('');
			Logger::info(console_color("Step[$step_counter/$total_command] $title", 'green'));
			call_user_func([self::class, self::$commands[$step_cmd][0]]);
			$step_counter++;
		}
		Logger::info('---- Project Init Done ----');
	}

	private static function projectEnvConfirm(){
		$envs = self::getProjectInfo();
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
		if(!console_confirm("Is these information correct?")){
			die('Exit');
		}
	}

	private static function getProjectRoot(){
		return dirname(self::getVendorDir());
	}

	private static function getComposerInfo($path = ''){
		$json = json_decode(file_get_contents(self::getProjectRoot().'/composer.json'), true);
		return array_get($json, $path);
	}

	public static function getProjectInfo(){
		$app_root = self::getProjectRoot();
		$package_name = self::getComposerInfo('name');
		$ns = explode('/', $package_name);
		$app_name = join(' ', $ns);
		foreach($ns as $k => $v){
			$ns[$k] = underscores_to_pascalcase($v, true);
		}
		$app_namespace = join('\\', $ns);

		$app_name_var = str_replace('\\', '', $app_namespace);
		return [
			'app_root'      => realpath($app_root),
			'app_name'      => $app_name,
			'app_name_var'  => $app_name_var,
			'app_namespace' => $app_namespace,
		];
	}

	/**
	 * 初始化必要的文件
	 * @return void
	 */
	public static function initFile($tag = 'tag-base'){
		$file_structs = self::getTemplateProjectStructure($tag);
		foreach($file_structs as $dir){
			if(!is_dir($dir)){
				if(!mkdir($dir, 0x777, true)){
					throw new \Exception('Make directory fail:'.$dir);
				}
				Logger::info('Directory created:', realpath($dir));
			}else{
				Logger::debug('Directory already exists.', realpath($dir));
			}
		}

		$overwrite = false;
		$file_map = self::getTemplateProjectFiles($tag);
		foreach($file_map as $src_file => [$target_file, $is_tpl]){
			if(!$overwrite && is_file($target_file)){
				Logger::debug('Target file exists: '.$target_file);
				continue;
			}
			Logger::info('Build File:', $target_file);
			if($is_tpl){
				$ctn = file_get_contents($src_file);
				if(strlen($ctn)){
					$hit = false;
					$ctn = self::mixingProjectInfo($ctn, $hit);
					if($hit){
						file_put_contents($target_file, $ctn);
						continue;
					}
				}
			}
			copy($src_file, $target_file);
		}
	}

	/**
	 * 初始化数据库配置
	 * @return void
	 */
	public static function initDatabase(){
		self::initFile('tag-database');

		$project_info = self::getProjectInfo();
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

	/**
	 * 使用项目环境变量替换字符串中的变量
	 * @param string $str
	 * @param bool $hit
	 * @return string
	 */
	private static function mixingProjectInfo($str, &$hit = false){
		$project_info = self::getProjectInfo();
		foreach($project_info as $k => $v){
			$c = 0;
			$str = str_replace('{'.$k.'}', $v, $str, $c);
			if($c){
				$hit = true;
			}
		}
		return $str;
	}

	public static function generateCRUD(){
		self::initFile('tag-crud');;
	}

	public static function generateORM(){
		self::initFile('tag-orm');
		$project_info = self::getProjectInfo();
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

	public static function generateIndexPage(){
	}

	public static function integratedFrontend(){
		self::initFile('tag-frontend');
	}

	/**
	 * @return array
	 */
	private static function getTemplateProjectStructure($tag){
		$tpl_dir = realpath(self::TEMPLATE_PROJECT."/$tag");
		$dirs = glob_recursive($tpl_dir."/*", GLOB_ONLYDIR);
		foreach($dirs as $k => $dir){
			$dirs[$k] = self::mixingProjectInfo(str_replace($tpl_dir, '{app_root}', realpath($dir)));
		}
		return $dirs;
	}

	/**
	 * @param $tag
	 * @return array
	 */
	private static function getTemplateProjectFiles($tag){
		$tpl_dir = realpath(self::TEMPLATE_PROJECT."/$tag");
		$dirs = glob_recursive($tpl_dir.'/*');
		$file_map = [//src_file => target_file
		];
		foreach($dirs as $k => $file){
			if(!is_file($file)){
				continue;
			}
			$is_template = !!preg_match('/\.tpl$/', $file);
			$src_file = realpath($file);
			$target_file = self::mixingProjectInfo(str_replace($tpl_dir, '{app_root}', realpath($file)));
			$target_file = preg_replace('/\.tpl$/', '', $target_file);
			$file_map[$src_file] = [$target_file, $is_template];
		}
		return $file_map;
	}
}
