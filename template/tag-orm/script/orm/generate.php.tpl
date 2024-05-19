<?php

use LFPhp\Logger\Logger;
use LFPhp\PDODSN\Database\MySQL;
use LFPhp\PORM\DB\DBDriver;
use LFPhp\PORM\ORM\Attribute;
use LFPhp\PORM\ORM\DSLHelper;
use function LFPhp\Func\check_php_var_name_legal;
use function LFPhp\Func\get_all_opt;
use function LFPhp\Func\pascalcase_to_underscores;
use function LFPhp\Func\underscores_to_pascalcase;
use function LFPhp\PLite\get_app_var_name;
use function LFPhp\PLite\get_config;

include_once __DIR__.'/../script.inc.php';

$opt = get_all_opt();
$usage = str_repeat('=', 30).PHP_EOL.<<<EOT
Generate Source Model
[Usage]
    php {$_SERVER['SCRIPT_NAME']} -h show this help
    php {$_SERVER['SCRIPT_NAME']} --source_id=sedoll/erp [REQUIRED] source id define.
    php {$_SERVER['SCRIPT_NAME']} --table=* [OPTIONAL] generate all table, default for all table
[Quick Start]
    php {$_SERVER['SCRIPT_NAME']} --source_id=sedoll/erp
EOT;
if(isset($opt['h'])){
	die($usage);
}

$specified_source_id = $opt['source_id'];

$ns_prefix = get_app_var_name();

if($specified_source_id && ucfirst($specified_source_id) !== $specified_source_id){
	Logger::info("Source key [$specified_source_id] correct to ".ucfirst($specified_source_id));
	$specified_source_id = ucfirst($specified_source_id);
}

if(!$specified_source_id){
	$all_db_config = get_config('database');
	$source_ids = array_map('ucfirst', array_keys($all_db_config));
	Logger::warning('Using all source id, all source id corrected: '.join(", ", $source_ids));
}else{
	$source_ids = [$opt['source_id']];
}

foreach($source_ids as $source_id){
	Logger::info('>>>>>>>>>>>> Start processing source:'.$source_id.' <<<<<<<<<<<<<<<<<<');

	$db_config = get_config("database/$source_id");
	if(!$db_config){
		throw new Exception('Database config no found:'.$source_id);
	}

	$OUTPUT_TABLE_DIR = PLITE_APP_ROOT."/src/Business/$source_id/Table";
	$OUTPUT_MODEL_DIR = PLITE_APP_ROOT."/src/Business/$source_id/Model";
	$MODEL_BASE_FILE = PLITE_APP_ROOT."/src/Business/$source_id/ModelBase.php";

	$output_table_dir = $OUTPUT_TABLE_DIR;
	!is_dir($output_table_dir) && mkdir($output_table_dir, 0777, true);

	$output_model_dir = $OUTPUT_MODEL_DIR;
	!is_dir($output_model_dir) && mkdir($output_model_dir, 0777, true);

	if(!is_file($MODEL_BASE_FILE)){
		$output = buildModelBaseTemplate($source_id, $ns_prefix);
		$ret = file_put_contents($MODEL_BASE_FILE, $output);
		if($ret){
			Logger::info("model base generated: $MODEL_BASE_FILE");
		}
	}

	Logger::info('Connecting to '.$source_id);
	$dsn = new MySQL($db_config);

	Logger::info('fetching tables');
	if(!isset($opt['table']) || $opt['table'] == '*'){
		$tables = DBDriver::instance($dsn)->getTables();
		$tables = array_column($tables, 'table_name');
	}else{
		$tables = explode(',', $opt['table']);
	}

	Logger::info('Start to analysis table schema');

	$ignores = [];
	foreach($tables as $table){
		$table_adjust = tableFix($table);
		if($table_adjust !== $table && isset($ignores[$table_adjust])){
			Logger::warning('Table ignore.', $table);
			continue;
		}
		list($_, $table_desc, $attrs) = DSLHelper::getTableInfoByDSN($dsn, $table);
		$ignores[$table_adjust] = true;
		$table_file = $output_table_dir.'/'.underscores_to_pascalcase($table_adjust, true).'Table.php';
		$output = buildTableTemplate($source_id, $ns_prefix, $table_adjust, $table_desc, $attrs);
		$ret = file_put_contents($table_file, $output);
		if($ret){
			Logger::info("table generated: $table ==> ".underscores_to_pascalcase($table_adjust, true), "File:".$table_file);
		}

		$model_file = $output_model_dir.'/'.underscores_to_pascalcase($table_adjust, true).'.php';

		//忽略已经有的 Model 文件
		if(is_file($model_file)){
			Logger::info("model file already exists.", $model_file);
		}else{
			$output = buildModelTemplate($source_id, $ns_prefix, $table_adjust, $table_desc, $attrs);
			$ret = file_put_contents($model_file, $output);
			Logger::info("Model generated: $table ==> ".underscores_to_pascalcase($table_adjust, true), "File:".$model_file);
		}
	}
	usleep(100*1000);
}

echo "ALL DONE";

function tableFix($table){
	//	$table = preg_replace('/[_\d]+$/','', $table);
	//	$table = preg_replace('/^t_/i', '', $table);
	return $table;
}

function buildTableTemplate($source_id, $ns_prefix, $table, $table_description, $attributes){
	ob_start();
	include __DIR__.'/table.tpl.php';
	$str = ob_get_contents();
	ob_end_clean();
	return $str;
}

function buildModelBaseTemplate($source_id){
	ob_start();
	include __DIR__.'/modelbase.tpl.php';
	$str = ob_get_contents();
	ob_end_clean();
	return $str;
}

function buildModelTemplate($source_id, $ns_prefix, $table, $table_description, $attributes){
	ob_start();
	include __DIR__.'/model.tpl.php';
	$str = ob_get_contents();
	ob_end_clean();
	return $str;
}

/**
 * 枚举属性转换成为常量
 * @param \LFPhp\PORM\ORM\Attribute $attr
 * @param array $const_list
 * @return bool
 */
function enum_to_const(Attribute $attr, &$const_list = []){
	if($attr->type !== Attribute::TYPE_ENUM && $attr->type !== Attribute::TYPE_SET){
		return false;
	}
	if(!$attr->options){
		return false;
	}
	$const_list = [];
	foreach($attr->options as $opt_val => $opt_alias){
		if($var_name = check_php_var_name_legal($opt_val)){
			$var_name = strtoupper(pascalcase_to_underscores($attr->name.'_'.$var_name));
			$const_list[$var_name] = [$opt_val, $opt_alias];
		}
	}
	return true;
}

