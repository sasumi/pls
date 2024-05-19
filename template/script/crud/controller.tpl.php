<?php

/** @var string $model */
/** @var array $operate_types */
/** @var LFPhp\PORM\ORM\Model $full_model_class */

use function LFPhp\PLite\get_app_namespace;
use function LFPhp\PLite\get_app_var_name;

$full_model_class = get_model_class($model);
$model_title = $full_model_class::getModelDesc();
$model_lowercase = strtolower($model);
$model_alias = $model.'Model';
$uri = "$model_lowercase/index";
$attrs = $full_model_class::getAttributes();
$project_ns = get_app_namespace();
$project_var_name = get_app_var_name();
$pk_field = $full_model_class::getPrimaryKey();
echo '<?php', PHP_EOL;
?>
namespace <?=$project_ns;?>\Http\Controller;

use <?=$project_ns;?>\Http\AuthorizeRequired;
use <?=$project_ns;?>\Http\Controller;
use <?=$project_ns;?>\Business\<?=$project_var_name;?>\Model\<?=$model;?> as <?=$model_alias;?>;
use <?=$project_ns;?>\Lib\Paginate;
use function LFPhp\Func\request_in_post;

class <?=$model;?> extends Controller {
	use AuthorizeRequired;

<?php if($operate_types['index']):?>
	/**
	* 列表
	* @return array
	* @throws \LFPhp\PORM\Exception\DBException
	* @throws \LFPhp\PORM\Exception\Exception
	*/
	public function index($search){
		$paginate = Paginate::instance();
		$list = <?=$model_alias;?>::find()->order('id DESC')->paginate($paginate);
		return compact('list', 'search', 'paginate');
	}
<?php endif;?>

<?php if($operate_types['create']):?>
	/**
	* 创建
	* @return int
	* @throws \LFPhp\PORM\Exception\DBException
	* @throws \LFPhp\PORM\Exception\Exception
	*/
	public function create($request){
		if(request_in_post()){
			$obj = <?=$model_alias;?>::create([
<?php foreach($attrs as $attr):if($attr->is_primary_key){continue;}?>
				'<?=$attr->name;?>' => $request['<?=$attr->name;?>'],
<?php endforeach;?>
			]);
			return $obj->id;
		}
	}
<?php endif;?>

<?php if($operate_types['info']):?>
	/**
	* 详情
	* @return array
	* @throws \LFPhp\PORM\Exception\DBException
	* @throws \LFPhp\PORM\Exception\Exception
	*/
	public function info(){
		$<?=$pk_field;?> = $_GET['<?=$pk_field;?>'];
		$obj = <?=$model_alias;?>::findOneByPkOrFail($<?=$pk_field;?>);
		return ['model'=>$obj];
	}
<?php endif;?>

<?php if($operate_types['update']):?>
	/**
	* 编辑
	* @throws \LFPhp\PORM\Exception\DBException
	* @throws \LFPhp\PORM\Exception\Exception
	*/
	public function update($request){
		$<?=$pk_field;?> = $request['<?=$pk_field;?>'];
		if(request_in_post()){
			<?=$model_alias;?>::updateByPk($<?=$pk_field;?>, [
<?php foreach($attrs as $attr):if($attr->is_primary_key){continue;}?>
				'<?=$attr->name;?>' => $request['<?=$attr->name;?>'],
<?php endforeach;?>
			]);
		}
		return ['<?=$pk_field;?>' => $<?=$pk_field;?>];
	}
<?php endif;?>

<?php if($operate_types['delete']):?>
	/**
	* 删除
	* @throws \LFPhp\PORM\Exception\DBException
	* @throws \LFPhp\PORM\Exception\Exception
	*/
	public function delete($request){
		$id = $request['id'];
		<?=$model_alias;?>::delByPkOrFail($id);
	}
<?php endif;?>
}
