<?php

use function LFPhp\PLite\get_app_namespace;

/** @var string $source_id */

$project_ns = get_app_namespace();
echo '<?php', PHP_EOL;
?>

namespace <?=$project_ns;?>\Business\<?=$source_id;?>;

use LFPhp\PDODSN\Database\MySQL;
use LFPhp\PORM\ORM\Model;
use function LFPhp\PLite\get_config;

abstract class ModelBase extends Model {

	/**
	* get DSN
	* @param int $operate_type
	* @return \LFPhp\PDODSN\DSN
	* @throws \Exception
	*/
	public static function getDbDsn($operate_type = self::OP_READ){
		return new MySQL(get_config('database')['<?=$source_id;?>']);
	}
}
