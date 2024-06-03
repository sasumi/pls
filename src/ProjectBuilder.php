<?php

namespace LFPhp\Pls;

use Composer\InstalledVersions;
use LFPhp\Logger\Logger;
use LFPhp\Logger\LoggerLevel;
use LFPhp\Logger\Output\ConsoleOutput;
use LFPhp\Pls\command\Cmd;
use function LFPhp\Func\array_get;
use function LFPhp\Func\console_color;
use function LFPhp\Func\get_all_opt;
use function LFPhp\Func\glob_recursive;
use function LFPhp\Func\underscores_to_pascalcase;

class ProjectBuilder {
	const TEMPLATE_PROJECT = __DIR__.'/../template';
	public static $app_root;

	public static function start(){
		Logger::registerGlobal(new ConsoleOutput(), LoggerLevel::DEBUG);

		$args = get_all_opt();
		Logger::debug('Arguments detected: ', $args);
		array_shift($args);

		$cmd = array_shift($args);
		$all_commands = Cmd::getAllCmd();
		echo "\n".str_repeat('=',40)."\n".console_color(' PLite Project Builder ', 'white', 'yellow')."\n";
		echo "Version: ".InstalledVersions::getVersion('lfphp/pls')."\n";
		echo str_repeat("-", 40)."\n";
		if(!$all_commands[$cmd]){
			Cmd::runCmd('help');
		}else{
			Cmd::runCmd($cmd);
		}
	}

	public static function addGitIgnore($rules){
		$root = self::getProjectRoot();
		$git_dir = $root.'/.git';
		$git_ignore_file = $root.'/.git';
		if(is_dir($git_dir)){
			$str = '';
			foreach($rules as $rule){
				$str .= "\n".$rule;
			}
			$org_content = is_file($git_ignore_file) ? trim(file_get_contents($git_ignore_file)) : '';
			file_put_contents($git_ignore_file, $org_content."\n".$str, FILE_APPEND);
			return true;
		}
		return false;
	}

	private static function getComposerInfo($path = ''){
		$json = json_decode(file_get_contents(self::$app_root.'/composer.json'), true);
		return array_get($json, $path);
	}

	public static function getProjectRoot(){
		return self::getProjectInfo()['app_root'];
	}

	public static function getProjectInfo(){
		$package_name = self::getComposerInfo('name');
		$ns = explode('/', $package_name);
		$app_name = join(' ', $ns);
		foreach($ns as $k => $v){
			$ns[$k] = underscores_to_pascalcase($v, true);
		}
		$app_namespace = join('\\', $ns);

		$app_name_var = str_replace('\\', '', $app_namespace);
		return [
			'app_root'      => realpath(self::$app_root),
			'app_name'      => $app_name,
			'app_name_var'  => $app_name_var,
			'app_namespace' => $app_namespace,
		];
	}

	/**
	 * 初始化必要的文件
	 * @return void
	 */
	public static function initFile($tag){
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

		$opts = get_all_opt();
		$overwrite = isset($opts['w']);
		$file_map = self::getTemplateProjectFiles($tag);
		foreach($file_map as $src_file => [$target_file, $is_tpl]){
			$file_exists = is_file($target_file);
			if(!$overwrite && $file_exists){
				Logger::debug('Target file exists: '.$target_file);
				continue;
			}
			if($file_exists){
				Logger::warning('File Override:', $target_file);
			} else {
				Logger::info('Build File:', $target_file);
			}

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

	/**
	 * @return array
	 */
	private static function getTemplateProjectStructure($tag){
		$tpl_dir = realpath(self::TEMPLATE_PROJECT."/$tag");
		if(!$tpl_dir){
			throw new \Exception('template dir no exist: '.self::TEMPLATE_PROJECT."/$tag");
		}
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
		foreach($dirs as $file){
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
