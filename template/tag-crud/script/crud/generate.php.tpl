<?php

use LFPhp\Logger\Logger;
use LFPhp\PORM\ORM\Attribute as Attribute;
use function LFPhp\Func\explode_by;
use function LFPhp\Func\get_all_opt;
use function LFPhp\Func\render_php_file;
use function LFPhp\PLite\get_app_namespace;
use function LFPhp\PLite\get_app_var_name;
use function LFPhp\PLite\get_config;

include_once __DIR__.'/../script.inc.php';

const TYPES_MAP = ['index' => '列表', 'info' => '详情', 'create' => '新增', 'update' => '编辑', 'delete' => '删除',];

$opt = get_all_opt();
$usage = str_repeat('=', 30).PHP_EOL.<<<EOT
Generate Source Model
[Usage]
    php {$_SERVER['SCRIPT_NAME']} -h show this help
    php {$_SERVER['SCRIPT_NAME']} --models=SysUser [REQUIRED] specifiy models, seperated by comma
    php {$_SERVER['SCRIPT_NAME']} --route=admin/sysuser [OPTIONAL] route rule（exp: --route=sysuser/*，default by lowercase of model
    php {$_SERVER['SCRIPT_NAME']} --types=index,info,create,update,delete [OPTIONAL]
    php {$_SERVER['SCRIPT_NAME']} -o [OPTIONAL] overwrite
[Quick Start]
    php {$_SERVER['SCRIPT_NAME']} --model=SysUser
EOT;
if(!$opt['models'] || isset($opt['h'])){
	die($usage);
}

$overwrite = isset($opt['o']);
$types = $opt['types'];
$models = explode_by(',', $opt['models']);
Logger::info('Start to generate CRUD');

foreach($models as $model){
	$model_lowercase = strtolower($model);
	$full_model_class = get_model_class($model);
	$full_controller_class = get_controller_class($model);

	$routes = [];
	if($opt['route']){
		$routes = [$opt['route']];
	}else if($types){
		foreach($types as $t){
			$routes[] = $model_lowercase.'/'.$t;
		}
	}else{
		$routes = [$model_lowercase.'/*'];
	}
	$types = $types ?: ['index', 'info', 'create', 'update', 'delete'];
	$operate_types = [];
	foreach($types as $t){
		$operate_types[$t] = TYPES_MAP[$t];
	}

	Logger::info('Model:'.$model, 'types:'.join(',', $types), 'route:'.join(',', $routes));

	Logger::info("\n");
	Logger::debug(">>>> ROUTE");
	$tmp = get_config('routes');
	if(isset($tmp[$model_lowercase.'/*'])){
		Logger::error('supper router rule already exists, ignore:'.$model_lowercase.'/*');
	}else{
		$route_patches = [];
		foreach($routes as $r){
			if(isset($tmp[$r])){
				Logger::info('router rule already exists, ignore:'.$r);
			}else if($r === $model_lowercase.'/*'){
				$route_patches[] = "'$r' => $full_controller_class::class.'@*'";
			}else{
				$method = explode('/', $r)[1];
				$route_patches[] = "'$r' => $full_controller_class::class.'@$method'";
			}
		}
		Logger::warning("routes to patch:\n", join("\n", $route_patches));
		$route_str = file_get_contents(PLITE_CONFIG_PATH.'/routes.inc.php');
		$last_sq_seg_pos = strpos($route_str, '(');
		$last_mq_seg_pos = strpos($route_str, '[');
		if($last_sq_seg_pos !== false){
			$route_str = substr($route_str, 0, $last_sq_seg_pos + 1)."\n".join("\n", $route_patches).",".substr($route_str, $last_sq_seg_pos + 1);
		}else if($last_mq_seg_pos !== false){
			$route_str = substr($route_str, 0, $last_mq_seg_pos + 1)."\n".join("\n", $route_patches).",".substr($route_str, $last_mq_seg_pos + 1);
		}else{
			throw new Exception('router patch fail, content resolve fail:'.$route_str);
		}
		file_put_contents(PLITE_CONFIG_PATH.'/routes.inc.php', $route_str);
		Logger::warning('route patch success');
	}

	Logger::info("\n");
	Logger::debug('>>>> CONTROLLER');
	$controller_dir = PLITE_APP_ROOT.'/src/Http/Controller';
	!is_dir($controller_dir) && mkdir($controller_dir, 0755, true);
	Logger::info('Controller Fold:'.$controller_dir);

	$controller_file = $controller_dir."/$model.php";
	$controller_exists = is_file($controller_file);
	if(!$controller_exists || $overwrite){
		$str = render_php_file(__DIR__.'/controller.tpl.php', compact('model', 'operate_types'));
		file_put_contents($controller_file, $str);
		$controller_exists && Logger::warning('controller overwrite:'.$controller_file);
		!$controller_exists && Logger::info('controller created:'.$controller_file);
	}else if($controller_exists && !$overwrite){
		Logger::warning('controller already exists, ignored:'.$controller_file);
	}

	$page_path = PLITE_PAGE_PATH.'/'.$model_lowercase;
	!is_dir($page_path) && mkdir($page_path, 0755, true);
	Logger::info('Template Fold:'.$page_path);

	foreach($operate_types as $type => $title){
		if($type === 'delete'){
			continue;
		}
		Logger::info("\n");
		Logger::debug(">>>> $type");
		$page = $page_path."/$type.php";
		$template = __DIR__."/$type.tpl.php";

		if(!is_file($template)){
			Logger::warning("operate no support:".$type);
			continue;
		}

		$page_exists = is_file($page);
		if(!$page_exists || $overwrite){
			$str = render_php_file($template, compact('model', 'operate_types'));
			file_put_contents($page, $str);
			$page_exists && Logger::warning('page overwrite:'.$page);
			!$page_exists && Logger::info('page created:'.$page);
		}else if($page_exists && !$overwrite){
			Logger::warning("$type template already exists, ignored:".$page);
		}
	}
}

echo "ALL DONE";

function get_model_class($class){
	$var_name = get_app_var_name();
	return '\\'.get_app_namespace()."\\Business\\$var_name\\Model\\$class";
}

function get_controller_class($class){
	return '\\'.get_app_namespace()."\\Http\\Controller\\$class";
}

function html_render_attribute_element(Attribute $attr, $value_code = ''){
	$required_attr = $attr->is_null_allow ? '' : 'required';
	$input_id = "field-".$attr->name;
	$input_name = 'name="'.$attr->name.'"';
	switch($attr->type){
		case Attribute::TYPE_INT:
		case Attribute::TYPE_FLOAT:
		case Attribute::TYPE_DECIMAL:
		case Attribute::TYPE_DOUBLE:
			$precise = 1;
			return "<input type=\"number\" $input_name id=\"$input_id\" step=\"$precise\" value=\"$value_code\" $required_attr/>";

		case Attribute::TYPE_STRING:
			return "<input type=\"text\" $input_name id=\"$input_id\" value=\"$value_code\" $required_attr/>";

		case Attribute::TYPE_SET:
		case Attribute::TYPE_ENUM:
			$html = '';
			$comma = '';
			$input_type = $attr->type == Attribute::TYPE_SET ? 'checkbox' : 'radio';
			foreach($attr->options as $field => $title){
				$html .= $comma."<label><input type=\"$input_type\" $input_name value=\"{$field}\" $value_code/> {$title}</label>";
				$comma = PHP_EOL."\t\t\t";
			}
			return $html;
		case Attribute::TYPE_BOOL:

		case Attribute::TYPE_DATE:
			return "<input type=\"date\" $input_name id=\"$input_id\" value=\"$value_code\" $required_attr/>";

		case Attribute::TYPE_TIME:
			return "<input type=\"time\" $input_name id=\"$input_id\" value=\"$value_code\" $required_attr/>";

		case Attribute::TYPE_DATETIME:
		case Attribute::TYPE_TIMESTAMP:
			return "<input type=\"datetime-local\" $input_name id=\"$input_id\" value=\"$value_code\" $required_attr/>";

		case Attribute::TYPE_YEAR:
			return "<input type=\"year\" $input_name id=\"$input_id\" value=\"$value_code\" $required_attr/>";
		default:
			throw new Exception('Attribute type no support'.$attr->type);
	}
}
