<?php

use LFPhp\PORM\ORM\Attribute;
use LFPhp\PORM\ORM\DSLHelper;
use function LFPhp\Func\pascalcase_to_underscores;
use function LFPhp\Func\underscores_to_pascalcase;
use function LFPhp\PLite\get_app_namespace;

/** @var string $ns_prefix */
/** @var string $source_id */
/** @var string $table */
/** @var bool $is_dynamic_table */
/** @var string $table_description */
/** @var Attribute[] $attributes */

$project_ns = get_app_namespace();

echo '<?php', PHP_EOL;
?>
namespace <?php echo $project_ns;?>\Business\<?=$ns_prefix;?>\Table;
use LFPhp\PORM\ORM\Attribute;
use <?php echo $project_ns;?>\Business\<?=$ns_prefix;?>\ModelBase;

/**
 * <?php echo $table_description, PHP_EOL;?>
<?php echo DSLHelper::convertAttrsToDoctrine($attributes);?>
 */
class <?php echo underscores_to_pascalcase($table, true);?>Table extends ModelBase {
<?php if($is_dynamic_table):?>
	private static $_current_table_name;
<?php endif;?>
<?php /** ENUM to CONST */
	foreach($attributes as $attr):
		if(enum_to_const($attr, $const_list)):
			$const_str = "";
foreach($const_list as $var_name=>list($var_val, $var_alias)):?>
	const <?=$var_name;?> = '<?=$var_val;?>';
<?php endforeach;?>

	const <?=strtoupper(pascalcase_to_underscores($attr->name));?>_MAP = [
<?php foreach($const_list as $var_name=>list($var_val, $var_alias)):?>
		self::<?=$var_name;?> => '<?=$var_alias;?>',
<?php endforeach;?>
	];
<?php endif;
	endforeach;
?>

	/**
	 * 获取属性列表
	 * @return Attribute[]
	 */
	public static function getAttributes(){
		return [
<?php foreach($attributes as $attr): ?>
			'<?=$attr->name;?>' => <?=DSLHelper::convertAttrToCode($attr, false);?>,
<?php endforeach;?>
		];
	}

<?php if($is_dynamic_table):?>
	public static function setDynamicTableName(/** dynamic param defines **/){
		self::$_current_table_name = '<?php echo $table;?>';
	}
<?php else:?>
	public static function getTableName(){
		return '<?php echo $table;?>';
	}
<?php endif;?>

	public static function getModelDesc(){
		return '<?php echo $table_description ?: $table;?>';
	}
}
