<?php

use function LFPhp\Func\underscores_to_pascalcase;
use function LFPhp\PLite\get_app_namespace;

/** @var string $ns_prefix */
/** @var string $source_id */
/** @var string $table */
/** @var bool $is_dynamic_table */

$project_ns = get_app_namespace();
echo '<?php', PHP_EOL;
?>
namespace <?=$project_ns;?>\Business\<?=$ns_prefix;?>\Model;
use <?=$project_ns;?>\Business\<?=$ns_prefix;?>\Table\<?php echo underscores_to_pascalcase($table, true);?>Table;

class <?php echo underscores_to_pascalcase($table, true);?> extends <?php echo underscores_to_pascalcase($table, true);?>Table {

}
