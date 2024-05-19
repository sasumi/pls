<?php

use function LFPhp\Func\h;
use function LFPhp\Func\ha;
use function LFPhp\PLite\get_app_namespace;

/** @var string $model */
/** @var array $operate_types */
/** @var LFPhp\PORM\ORM\Model $full_model_class */
$full_model_class = get_model_class($model);
$model_title = $full_model_class::getModelDesc();
$model_lowercase = strtolower($model);
$uri = "$model_lowercase/index";
$attrs = $full_model_class::getAttributes();
$project_ns = get_app_namespace();
$pk_name = $full_model_class::getPrimaryKey();

////////////// template start //////////////
echo '<?php', PHP_EOL;
?>
use <?=$project_ns;?>\Lib\Paginate;
use function LFPhp\Func\h;
use function LFPhp\Func\ha;
use function LFPhp\PLite\include_page;
use function LFPhp\PLite\url;
use function LFPhp\PLite\url_input;

/** @var array $search */
/** @var Paginate $paginate */
/** @var <?=$full_model_class;?>[] $list */
include_page('header.inc.php');
?>
<div class="content">
	<h2 class="page-cap"><?=$model_title;?>列表</h2>
	<form action="<?='<?='?>url('<?=$uri;?>');?>" method="get" class="filter-form">
<?='<?='?>url_input('<?=$uri;?>');?>
		<div class="form-row">
			<label>关键字：<input type="search" name="kw" value="<?='<?='?>ha($search['kw']);?>"></label>
		</div>
		<div class="form-row">
			<input type="submit" value="查询"> <input type="reset" class="outline-button" value="重置">
		</div>
	</form>
<?php if($operate_types['create']):?>
	<div class="operate-bar">
		<a href="<?='<?=';?>url('<?=$model_lowercase;?>/create');?>" data-component="dialog" class="create-button button"><?=$operate_types['create'];?></a>
	</div>
<?php endif;?>
	<table class="data-table fixed-head" data-component="empty">
		<thead>
			<tr>
<?php foreach($attrs as $attr):if($attr->is_primary_key){continue;}?>
				<th><?=ha($attr->alias ?: $attr->name);?></th>
<?php endforeach;?>
				<th>操作</th>
			</tr>
		</thead>
		<tbody>
		<?='<?'?>php foreach($list as $item):?>
			<tr>
<?php foreach($attrs as $attr):if($attr->is_primary_key){continue;}?>
				<td><?='<?=h('?>$item-><?=$attr->name;?>)?></td>
<?php endforeach;?>
				<td>
					<ul class="operate-list">
<?if($operate_types['update']):?>
						<li><a href="<?='<?=';?>url('<?=$model_lowercase.'/update';?>', ['<?=$pk_name;?>'=>$item-><?=$pk_name;?>])?>" data-component="dialog"><?=h($operate_types['update']);?></a></li>
<?php endif;?>
<?if($operate_types['info']):?>
						<li><a href="<?='<?=';?>url('<?=$model_lowercase.'/info';?>', ['<?=$pk_name;?>'=>$item-><?=$pk_name;?>])?>" data-component="dialog"><?=h($operate_types['info']);?></a></li>
<?php endif;?>
<?if($operate_types['delete']):?>
						<li><a href="<?='<?=';?>url('<?=$model_lowercase.'/delete';?>', ['<?=$pk_name;?>'=>$item-><?=$pk_name;?>])?>" data-component="confirm,async"><?=h($operate_types['delete']);?></a></li>
<?php endif;?>
					</ul>
				</td>
			</tr>
		<?='<?'?>php endforeach;?>
		</tbody>
	</table>
	<?='<?='?>$paginate;?>
</div>
<?='<?'?>php include_page('footer.inc.php');?>
