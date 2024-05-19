<?php

use LFPhp\Logger\Logger;
use function LFPhp\Func\get_all_opt;
use function LFPhp\Func\glob_recursive;
use function LFPhp\Func\resolve_file_extension;

include_once __DIR__.'/script.inc.php';

$target = PLITE_CONFIG_PATH.'/'.PLITE_STATIC_VERSION_CONFIG_FILE.'.inc.php';
$static_dir = [
	PLITE_APP_ROOT.'/public',
];

$opt = get_all_opt();
$clean = in_array('clean', $opt);

$static_map = [//
	//file => [uri, md5_8]
];
foreach($static_dir as $dir){
	$files = glob_recursive($dir.'/*');
	$dir_r = realpath($dir);
	foreach($files as $file){
		if(!is_file($file) || preg_match('/\.php$/i', $file)){
			continue;
		}
		$uri = str_replace([$dir_r, '\\'], ['', '/'], $file);
		$uri = trim($uri, '/');
		$ver = $clean ? '' : substr(md5_file($file), 0, 8);
		$static_map[$file] = [$uri, $ver];
	}
}

$version_map = [];
Logger::info('start update static version config');
foreach($static_map as $file => [$uri, $md5]){
	$version_map[$uri] = $md5;
}
file_put_contents($target, config_file_beauty($version_map));
Logger::info('static file updated', $target);

Logger::info('start update version in files');

//需要处理的文件包含静态文件、PHP模板文件
$process_files = array_keys($static_map);
$tmp = glob_recursive(PLITE_APP_ROOT.'/src/page/*.php');
$process_files = array_merge($process_files, $tmp);

foreach($process_files as $file){
	Logger::debug('process file:'.$file);
	$ctn = file_get_contents($file);
	$file_type = resolve_file_extension($file);

	if($file_type === 'php'){
		$fie_base_dir = PLITE_APP_ROOT.'/public';
	} else {
		$fie_base_dir = dirname($file);
	}

	if(!in_array($file_type, ['css', 'js', 'php'])){
		Logger::debug('ignore by file type:', $file);
		continue;
	}

	//js
	$hit = false;
	$new_ctn = preg_replace_callback('/(import.*?from\s+)"([^"]+)"/is', function($matches) use ($file, $static_map, $fie_base_dir, &$hit){
		$ret = replace_static_file($matches[2], $fie_base_dir, $static_map);
		$hit = !!$ret;
		return $ret ? $matches[1]."\"$ret\"" : $matches[0];
	}, $ctn);
	if($hit){
		file_put_contents($file, $new_ctn, 1);
		Logger::warning('file updated', $file, "\n");
	}

	//css
	$hit = false;
	$new_ctn = preg_replace_callback('/\@import\s+"([^"]+)"/is', function($matches) use ($file, $static_map, $fie_base_dir, &$hit){
		$matches[1] = ltrim($matches[1], '/');
		$ret = replace_static_file($matches[1], $fie_base_dir, $static_map);
		$hit = !!$ret;
		return $ret ? "@import \"$ret\"" : $matches[0];
	}, $ctn);
	if($hit){
		file_put_contents($file, $new_ctn, 1);
		Logger::warning('file updated', $file);
	}

}

Logger::info('processing template file');

/**
 * @param string $file_uri 静态文件uri，如 css/style.css 或 ./global.js
 * @param string $file_path 文件路径
 * @param array $fs_map 静态文件配置
 */
function replace_static_file($file_uri, $file_path, $fs_map){
	$old_ver = '';
	if(preg_match('/(.+)\?(.*)$/', $file_uri, $ms)){
		$file_uri = $ms[1];
		$old_ver = $ms[2];
	}
	$real_file = realpath($file_path.'/'.$file_uri);
	$current_ver = $fs_map[$real_file][1];

	//使用isset判断，支持空字符串重置
	if(!isset($current_ver)){
		Logger::warning('no version config found:'.$file_uri);
		return null;
	}
	if($old_ver === $current_ver){
		Logger::warning('version already exists', $old_ver);
		return null;
	}
	Logger::info("version updated $file_uri: ".($old_ver ?: '<empty>')." => $current_ver");
	return $file_uri.($current_ver ? '?'.$current_ver : '');
}

function config_file_beauty($arr){
	$str = "<?php\nreturn [\n";
	foreach($arr as $k => $v){
		$str .= "\t'".addslashes($k)."' => '".addslashes($v)."',\n";
	}
	$str .= "];";
	return $str;
}


