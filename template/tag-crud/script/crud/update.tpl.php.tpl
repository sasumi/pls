<?php

use function LFPhp\Func\h;
use function LFPhp\PLite\get_app_namespace;

/** @var string $model */
/** @var array $operate_types */
/** @var LFPhp\PORM\ORM\Model $full_model_class */
unset($operate_types['index']);
$full_model_class = get_model_class($model);
$model_title = $full_model_class::getModelDesc();
$model_lowercase = strtolower($model);
$uri = "$model_lowercase/update";
$attrs = $full_model_class::getAttributes();
$project_ns = get_app_namespace();
$pk_field = $full_model_class::getPrimaryKey();

////////////// template start //////////////
echo '<?php', PHP_EOL;
?>
use function LFPhp\Func\ha;
use function LFPhp\Func\h;
use function LFPhp\PLite\include_page;
use function LFPhp\PLite\url;
use <?=$full_model_class;?>;

/** @var string $<?=$pk_field;?> */
include_page('header.inc.php');
$model = <?=$model;?>::findOneByPkOrFail($<?=$pk_field;?>);
?>
<div class="content">
	<h2 class="page-cap">更新<?=$model_title;?></h2>
	<form action="<?='<?=';?>url('<?=$uri;?>', ['<?=$pk_field;?>'=>$model-><?=$pk_field;?>]);?>" class="form" method="post" data-component="async">
		<table class="form-table">
			<tbody>
<?php foreach($attrs as $attr):if($attr->is_readonly){continue;}?>
				<tr>
					<th>
						<label for="field-<?=$attr->name;?>"><?=h($attr->alias ?: $attr->name)?>：</label>
					</th>
					<td>
						<?=html_render_attribute_element($attr, '<?=ha($model->'.$attr->name.");?>").PHP_EOL;?>
					</td>
				</tr>
<?php endforeach;?>
				<tr>
					<th></th>
					<td>
						<input type="submit" value="保存">
						<input type="reset" value="重置">
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
<?='<?'?>php include_page('footer.inc.php');?>
