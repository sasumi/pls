<?php
namespace LFPhp\Pls;

use Exception;
use LFPhp\Logger\Logger;
use function LFPhp\Func\array_get;
use function LFPhp\Func\array_push_by_path;
use function LFPhp\Func\console_color;
use function LFPhp\Func\get_all_opt;
use function LFPhp\Func\glob_recursive;
use function LFPhp\Func\readline;
use function LFPhp\Func\underscores_to_pascalcase;
use function LFPhp\Func\var_export_min;

const PLS_PROJECT_ROOT = __DIR__.'/../../';
const PLS_INSTALLER_ROOT = __DIR__;

/**
 * white php config file (with return statement in file)
 * @param $file
 * @param $config
 * @return void
 */
function pls_write_php_config_file($file, $config){
	$cfg_arr_str = var_export_min($config, true);
	$str = <<<EOT
<?php 
return $cfg_arr_str;
EOT;
	pls_save_file($file, $str);
}

function pls_get_template_project_files($tag){
	$tpl_dir = realpath(PLS_INSTALLER_ROOT."/template/$tag");
	$dirs = glob_recursive($tpl_dir.'/*');
	$file_map = [//src_file => target_file
	];
	foreach($dirs as $file){
		if(!is_file($file)){
			continue;
		}
		$is_template = !!preg_match('/\.tpl$/', $file);
		$src_file = realpath($file);
		$target_file = pls_mixing_project_info(str_replace($tpl_dir, '{app_root}', realpath($file)));
		$target_file = preg_replace('/\.tpl$/', '', $target_file);
		$file_map[$src_file] = [$target_file, $is_template];
	}
	return $file_map;
}

function pls_get_template_project_structure($tag){
	$tpl_dir = realpath(PLS_INSTALLER_ROOT."/template/$tag");
	if(!$tpl_dir){
		throw new Exception('template dir no exist: '.PLS_INSTALLER_ROOT."/template/$tag");
	}
	$dirs = glob_recursive($tpl_dir."/*", GLOB_ONLYDIR);
	foreach($dirs as $k => $dir){
		$dirs[$k] = pls_mixing_project_info(str_replace($tpl_dir, '{app_root}', realpath($dir)));
	}
	return $dirs;
}

function pls_mixing_project_info($str, &$hit = false){
	$project_info = pls_get_project_info();
	foreach($project_info as $k => $v){
		$c = 0;
		$str = str_replace('{'.$k.'}', $v, $str, $c);
		if($c){
			$hit = true;
		}
	}
	return $str;
}

function pls_get_composer_info($path = ''){
	$json = json_decode(file_get_contents(PLS_PROJECT_ROOT.'/composer.json'), true);
	return array_get($json, $path);
}

function pls_update_project_info($new_name){
	$new_name = trim(strtolower($new_name));
	$json = json_decode(file_get_contents(PLS_PROJECT_ROOT.'/composer.json'), true);
	$json['name'] = $new_name;
	$json['type'] = 'project';
	array_push_by_path($json, 'autoload.psr-4.'.pls_package_name_to_ns($new_name).'\\', 'src/');
	pls_save_file(PLS_PROJECT_ROOT.'/composer.json', json_encode($json, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
	return true;
}

function pls_package_name_to_ns($package_name){
	$ns = explode('/', $package_name);
	foreach($ns as $k => $v){
		$ns[$k] = underscores_to_pascalcase($v, true);
	}
	return join('\\', $ns);
}

function pls_get_project_info(){
	$package_name = pls_get_composer_info('name');
	$app_namespace = pls_package_name_to_ns($package_name);
	$app_name_var = str_replace('\\', '', $app_namespace);
	return [
		'app_root'      => realpath(PLS_PROJECT_ROOT),
		'app_name'      => str_replace('/', ' ', $package_name),
		'app_name_var'  => $app_name_var,
		'app_namespace' => $app_namespace,
	];
}

function pls_get_database_config_file(){
	$project_info = pls_get_project_info();
	$app_root = $project_info['app_root'];
	$f = $app_root.'/config/database.inc.php';
	return is_file($f) ? $f : null;
}

function pls_get_all_database_config(){
	$database_file = pls_get_database_config_file();
	if(!$database_file){
		throw new \Exception('No database config file:'.$database_file);
	}
	$config = include $database_file;
	if(!$config || !is_array($config)){
		throw new \Exception('Database config empty: '.$database_file);
	}
	return $config;
}

function pls_get_all_commands(){
	$fs = [
		PLS_INSTALLER_ROOT.'/command/reset.php',
		PLS_INSTALLER_ROOT.'/command/help.php',
		PLS_INSTALLER_ROOT.'/command/check.php',
		PLS_INSTALLER_ROOT.'/command/init.php',
		PLS_INSTALLER_ROOT.'/command/base.php',
		PLS_INSTALLER_ROOT.'/command/orm.php',
		PLS_INSTALLER_ROOT.'/command/crud.php',
		PLS_INSTALLER_ROOT.'/command/database.php',
		PLS_INSTALLER_ROOT.'/command/front.php',
	];
	$cmd_list = [];
	foreach($fs as $f){
		$tmp = include $f;
		$cmd = basename($f, '.php');
		$cmd_list[$cmd] = [
			$tmp[0],
			$tmp[1],
		];
	}
	return $cmd_list;
}

function pls_run_cmd($cmd){
	$cmd_list = pls_get_all_commands();
	call_user_func($cmd_list[$cmd][1]);
}

function pls_get_all_models(){
	$models = [];
	$project_info = pls_get_project_info();
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

function pls_add_git_ignore($path, array $rules){
	$git_ignore_file = $path.'/.gitignore';
	$str = '';
	foreach($rules as $rule){
		$str .= "\n".$rule;
	}
	$org_content = is_file($git_ignore_file) ? trim(file_get_contents($git_ignore_file)) : '';
	pls_save_file($git_ignore_file, $org_content."\n".$str, FILE_APPEND);
	return true;
}

function pls_console_confirm($confirm_msg = ''){
	$opt = get_all_opt();
	if(isset($opt['y'])){
		echo console_color('Msg Auto Confirmed ['.$confirm_msg."]\n", 'brown');
		return true;
	}
	$confirm_msg = $confirm_msg."\nType [y] or [yes] to confirm: ";
	$input = readline(console_color($confirm_msg, 'yellow'));
	$input = trim(strtolower($input));
	return in_array($input, ['y', 'yes']);
}

/**
 * @param $msg
 * @param $trim
 * @return string
 */
function pls_console_read_required($msg, $trim = false){
	while(true){
		$input = readline(console_color($msg, 'yellow'));
		if($trim){
			$input = trim($input);
		}
		if($input){
			return $input;
		}
	}
}

function pls_add_config_items($config_file, array $lines, $auto_create_file = false){
	if(!file_exists($config_file)){
		if(!$auto_create_file){
			return;
		}
		touch($config_file);
	}
	$config_Str = trim(file_get_contents($config_file)) ?: '<?php return [];';
	$last_mq_seg_pos = strpos($config_Str, ']');
	if($last_mq_seg_pos !== false){
		$config_Str = substr($config_Str, 0, $last_mq_seg_pos)."\n".join("\n", $lines).substr($config_Str, $last_mq_seg_pos);
	}else{
		throw new Exception('config file patch fail, content resolve fail:'.$config_Str);
	}
	pls_save_file($config_file, $config_Str);
}

function pls_init_file($tag){
	$file_structs = pls_get_template_project_structure($tag);
	foreach($file_structs as $dir){
		if(!is_dir($dir)){
			pls_mkdir($dir, 0x665, true);
		}else{
			Logger::debug('Directory already exists:', realpath($dir));
		}
	}

	$opts = get_all_opt();
	$overwrite = isset($opts['w']);
	$file_map = pls_get_template_project_files($tag);
	foreach($file_map as $src_file => [$target_file, $is_tpl]){
		$file_exists = is_file($target_file);
		if(!$overwrite && $file_exists){
			Logger::debug('Target file exists:'.$target_file);
			continue;
		}
		if($file_exists){
			Logger::warning('File Override:', $target_file);
		}else{
			Logger::info('Create File:', $target_file);
		}

		if($is_tpl){
			$ctn = file_get_contents($src_file);
			if(strlen($ctn)){
				$hit = false;
				$ctn = pls_mixing_project_info($ctn, $hit);
				if($hit){
					pls_save_file($target_file, $ctn);
					continue;
				}
			}
		}
		pls_copy($src_file, $target_file);
	}
}

function pls_copy($src_file, $target_file){
	if(DRY_RUN){
		return true;
	}
	return copy($src_file, $target_file);
}

function pls_save_file($file, $content, $flag = null){
	if(DRY_RUN){
		return true;
	}
	return file_put_contents($file, $content, $flag);
}

function pls_mkdir($dir, $permission = null, $recursive = false){
	if(DRY_RUN || is_dir($dir)){
		return true;
	}
	Logger::info('Directory created:', realpath($dir));
	$ret = mkdir($dir, $permission, $recursive);
	if(!$ret){
		throw new Exception('Make directory fail:'.$dir);
	}
}
