<?php

use LFPhp\Logger\Logger;
use LFPhp\PDODSN\Database\MySQL;
use LFPhp\PORM\DB\DBDriver;
use LFPhp\PORM\ORM\Attribute;
use LFPhp\PORM\ORM\DSLHelper;
use function LFPhp\Func\check_php_var_name_legal;
use function LFPhp\Func\get_all_opt;
use function LFPhp\Func\pascalcase_to_underscores;
use function LFPhp\Func\render_php_file;
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

const TEMPLATE_TABLE = __DIR__.'/table.tpl.php';
const TEMPLATE_MODEL = __DIR__.'/model.tpl.php';
const TEMPLATE_MODEL_BASE = __DIR__.'/modelbase.tpl.php';

const OUTPUT_TABLE_DIR = PLITE_APP_ROOT."/src/Business/%s/Table";
const OUTPUT_MODEL_DIR = PLITE_APP_ROOT."/src/Business/%s/Model";
const OUTPUT_MODEL_BASE_FILE = PLITE_APP_ROOT."/src/Business/%s/ModelBase.php";

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

	$model_base_file = sprintf(OUTPUT_MODEL_BASE_FILE, $source_id);
	$output_table_dir = sprintf(OUTPUT_TABLE_DIR, $source_id);
	!is_dir($output_table_dir) && mkdir($output_table_dir, 0777, true);

	$output_model_dir = sprintf(OUTPUT_MODEL_DIR, $source_id);;
	!is_dir($output_model_dir) && mkdir($output_model_dir, 0777, true);

	if(!is_file($model_base_file)){
		$output = render_php_file(TEMPLATE_MODEL_BASE, compact('source_id'));
		$ret = file_put_contents($model_base_file, $output);
		if($ret){
			Logger::info("model base generated: $model_base_file");
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
		[$_, $table_description, $attributes] = DSLHelper::getTableInfoByDSN($dsn, $table);
		$ignores[$table_adjust] = true;
		$table_file = $output_table_dir.'/'.underscores_to_pascalcase($table_adjust, true).'Table.php';
		$output = render_php_file(TEMPLATE_TABLE, compact('source_id', 'table', 'table_description', 'attributes'));
		$ret = file_put_contents($table_file, $output);
		if($ret){
			Logger::info("table generated: $table ==> ".underscores_to_pascalcase($table_adjust, true), "File:".$table_file);
		}

		$model_file = $output_model_dir.'/'.underscores_to_pascalcase($table_adjust, true).'.php';

		//忽略已经有的 Model 文件
		if(is_file($model_file)){
			Logger::info("model file already exists.", $model_file);
		}else{
			$output = render_php_file(TEMPLATE_MODEL, compact('source_id', 'table', 'table_description', 'attributes'));
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

