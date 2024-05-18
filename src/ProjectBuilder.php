<?php

namespace LFPhp\Pls;

use Composer\Composer;
use Composer\InstalledVersions;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use LFPhp\Logger\Logger;
use LFPhp\Logger\LoggerLevel;
use LFPhp\Logger\Output\ConsoleOutput;
use function LFPhp\Func\console_color;
use function LFPhp\Func\glob_recursive;
use function LFPhp\Func\is_assoc_array;
use function LFPhp\Func\readline;
use function LFPhp\Func\run_command;
use function LFPhp\PLite\get_app_name;
use function LFPhp\PLite\get_app_namespace;

include_once dirname(__DIR__).'/vendor/autoload.php';

class ProjectBuilder implements PluginInterface {
	const TEMPLATE_PROJECT = __DIR__.'/../template';

	private static $steps = [
		'help'              => ['Command Helper'],
		'init'              => ['Init Project'],
		'projectEnvConfirm' => ['Project Configuration Confirm'],
		'initDir'           => ['Initialize project directories'],
		'initFile'          => ['Initialize project base file'],
		'initDatabase'      => ['Configuration Database'],
		'generateORM'       => ['Generate ORM'],
		'generateIndexPage' => ['Generate Index'],
	];

	public static function start(Event $event){
		Logger::registerGlobal(new ConsoleOutput(), LoggerLevel::DEBUG);
		$args = $event->getArguments();
		Logger::debug('Arguments detected: ', $args);
		$command = array_shift($args);
		$support_commands = array_map('strtolower', array_keys(self::$steps));
		if(!in_array($command, $support_commands)){
			self::help();
		}
		call_user_func([self::class, $command]);
	}

	public static function help(){
		$commands = [];
		foreach(self::$steps as $method => list($title)){
			$commands[] = console_color($method, 'brown')." \t\t\t\t".console_color($title, 'light_gray');
		}
		$helper_msg = "\n".console_color('PLite Project Builder', 'white', 'yellow')."\n"
			.console_color('[Usage]', 'green')."\n".
			"composer pls <command> [...arguments]\n\n".
			console_color('[Commands Support]', 'green')."\n".
			join("\n", $commands)."\n";
		die($helper_msg);
	}

	public static function init(){
		Logger::info('==== Start Init Project ====');

		$steps = [
			'projectEnvConfirm',
			'initDir',
			'initFile',
			'initDatabase',
			'generateORM',
			'generateIndexPage',
		];
		$step_counter = 1;
		$total_steps = count($steps);
		foreach($steps as $method){
			$title = self::$steps[$method][0];
			Logger::info("Step[$step_counter/$total_steps] $title");
			call_user_func([self::class, $method]);
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
		if(!console_confirm("Is that above information correct?")){
			die('Exit');
		}
	}

	public static function getProjectInfo(){
		$r = InstalledVersions::getRootPackage();
		$app_root = realpath($r['install_path']);
		$app_name = get_app_name();
		$app_namespace = get_app_namespace();
//
//		$app_root = dirname(__DIR__).'/test/hello';
//		$app_name = 'swing/hello';
//		$app_namespace = 'Swing\\Hello';

		$app_name_var = str_replace('\\', '', $app_namespace);
		return [
			'app_root'      => realpath($app_root),
			'app_name'      => $app_name,
			'app_name_var'  => $app_name_var,
			'app_namespace' => $app_namespace,
		];
	}

	/**
	 * 创建文件夹目录
	 * @return void
	 * @throws \Exception
	 */
	public static function initDir(){
		$file_structs = self::getTemplateProjectStructure();
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
	}

	/**
	 * 初始化必要的文件
	 * @return void
	 */
	public static function initFile(){
		$overwrite = false;
		$file_map = self::getTemplateProjectFiles();
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

	public static function generateORM(){
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
			$cmd = 'php '.realpath($app_root.'/script/orm/generate.php')." --source=$source_id";
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

	/**
	 * @return array
	 */
	private static function getTemplateProjectStructure(){
		$dirs = glob_recursive(self::TEMPLATE_PROJECT.'/*', GLOB_ONLYDIR);
		$tmpl_dir = realpath(self::TEMPLATE_PROJECT);
		foreach($dirs as $k => $dir){
			$dirs[$k] = self::mixingProjectInfo(str_replace($tmpl_dir, '{app_root}', realpath($dir)));
		}
		return $dirs;
	}

	private static function getTemplateProjectFiles(){
		$dirs = glob_recursive(self::TEMPLATE_PROJECT.'/*');
		$tmpl_dir = realpath(self::TEMPLATE_PROJECT);
		$file_map = [//src_file => target_file
		];
		foreach($dirs as $k => $file){
			if(!is_file($file)){
				continue;
			}
			$is_template = !!preg_match('/\.tpl$/', $file);
			$src_file = realpath($file);
			$target_file = self::mixingProjectInfo(str_replace($tmpl_dir, '{app_root}', realpath($file)));
			$target_file = preg_replace('/\.tpl$/', '', $target_file);
			$file_map[$src_file] = [$target_file, $is_template];
		}
		return $file_map;
	}

	public function activate(Composer $composer, IOInterface $io){
		// TODO: Implement activate() method.
	}

	public function deactivate(Composer $composer, IOInterface $io){
		// TODO: Implement deactivate() method.
	}

	public function uninstall(Composer $composer, IOInterface $io){
		// TODO: Implement uninstall() method.
	}
}
