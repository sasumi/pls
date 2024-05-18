<?php
namespace LFPhp\Pls;

use Exception;
use function LFPhp\Func\array_fetch_by_path;
use function LFPhp\Func\readline;

define('PLS_ROOT', dirname(__DIR__));

/**
 * @param string $template_name
 * @param array $params
 * @return string
 * @throws \Exception
 */
function mixing_template($template_name, $params = []){
	$template_file = PLS_ROOT.'/config/template/'.$template_name.'.template';
	if(!is_file($template_file)){
		throw new Exception('Template file no found:'.$template_name);
	}
	$str = file_get_contents($template_file);
	foreach($params as $k => $p){
		$str = str_replace("${$k}", $p, $str);
	}
	return $str;
}

function project_path($uri_pattern){
	return str_replace('$PROJECT_DIR', $project_dir, $uri_pattern);
}


/**
 * @param string $confirm_msg
 */
function console_confirm_to_continue($confirm_msg = ''){
	$retry_count = 3;
	$confirm_msg = $confirm_msg ?: "Please type [y] or [yes] to continue:";
	while(true){
		$input = readline($confirm_msg);
		if(trim($input) === 'y' || trim($input) === 'yes'){
			return;
		}
		if($retry_count-- == 0){
			die('Program exit on no response correctly.');
		}
	}
}


/**
 * get pls config
 * @param $name
 * @return array|mixed
 * @throws \Exception
 */
function get_pls_config($name){
	$args = explode('/', $name);
	$file = array_shift($args);

	$config_file = PLS_ROOT.'/config/'.$file.'.inc.php';
	if(!is_file($config_file)){
		throw new Exception('Config file no found:'.$config_file);
	}

	$data = include $config_file;
	if($args){
		return array_fetch_by_path($data, join('/', $args), null, '/');
	}
	return $data;
}
