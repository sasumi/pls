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
$uri = "$model_lowercase/index";
$attrs = $full_model_class::getAttributes();
$project_ns = get_app_namespace();

////////////// template start //////////////
echo '<?php', PHP_EOL;
?>
use function LFPhp\Func\h;
use function LFPhp\PLite\include_page;

/** @var <?=$full_model_class;?> $model */
include_page('header.inc.php');
?>
<div class="content">
	<h2 class="page-caption"><?=$model_title;?>详情</h2>
	<ul class="info-list">
<?php foreach($attrs as $attr):if($attr->is_primary_key){continue;}?>
		<li>
			<label class="info-label"><?=h($attr->alias ?: $attr->name)?>：</label>
			<span class="info-value">
				<?='<';?>?=h($model-><?=$attr->name;?>);?>
			</span>
		</li>
<?php endforeach;?>
	</ul>
</div>
<?='<?'?>php include_page('footer.inc.php');?>
