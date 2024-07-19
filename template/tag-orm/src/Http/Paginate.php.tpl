<?php

namespace {app_namespace}\Http;

use JsonSerializable;
use LFPhp\PORM\Misc\PaginateInterface;
use function LFPhp\PLite\get_router;
use function LFPhp\PLite\url;

/**
 * 分页
 */
class Paginate implements PaginateInterface, JsonSerializable {
	private $item_total;
	private $current_page;
	private $page_size; //当前生效 page_size，有可能不等于 config['page_size']
	private static $default_page_size = 10; //全局缺省每页条数

	private $config = [
		'page_key'               => 'page', //$_GET中页码key
		'page_size_key'          => 'page_size', //$_GET中每页条数key

		'page_size'              => 0, //每页条数，缺省由 {default_page_size} 设置
		'allow_change_page_size' => false, //是否允许更改每页条数
		'page_size_list'         => [10, 20, 50, 100, 200, 500, 1000, 2000], //每页条数选项列表，仅在 allow_change_page_size 生效时有效
		'num_offset'             => 4, //数字部分个数
		'item_limit'             => 0, //最大条数限制（0表示不限制）
	];

	private function __construct($config){
		$this->config = array_merge($this->config, $config);
		$page_key = $this->config['page_key'];
		$page_size_key = $this->config['page_size_key'];
		if(!$this->config['allow_change_page_size']){
			$this->page_size = $_REQUEST[$page_size_key] ?: $this->config['page_size'] ?: self::$default_page_size; //是否在URL中指定每页条数
		}else{
			$this->page_size = $this->config['page_size'] ?: self::$default_page_size; //是否在URL中指定每页条数
		}
		$this->current_page = max(isset($_REQUEST[$page_key]) ? intval($_REQUEST[$page_key]) : 1, 1); //当前页码
	}

	public static function instance($config = []){
		static $instance_list = [];
		$k = serialize($config);
		if(!isset($instance_list[$k])){
			$instance_list[$k] = new self($config);
		}
		return $instance_list[$k];
	}

	public static function setDefaultPageSize($page_size){
		self::$default_page_size = $page_size;
	}

	public function __toString(){
		$html = '';
		$total_page_count = ceil($this->item_total/$this->page_size);
		if($this->current_page > 1){
			$html .= '<a class="paginate-prev" title="上一页" href="'.$this->url($this->current_page - 1).'"></a>';
		}else{
			$html .= '<span class="paginate-prev"></span>';
		}
		$num_offset = 4;

		//前置部分
		if($this->current_page - $num_offset > 1){
			$html .= '<span class="paginate-dot"></span>';
		}
		for($i = min($num_offset, $this->current_page - 1); $i > 0; $i--){
			$p = $this->current_page - $i;
			$html .= '<a class="paginate-num" title="第'.$p.'页" href="'.$this->url($p).'">'.$p.'</a>';
		}
		$html .= '<span class="paginate-num paginate-current">'.$this->current_page.'</span>';

		//后置部分
		for($i = $this->current_page + 1; $i <= min($total_page_count, $this->current_page + $num_offset); $i++){
			$html .= '<a class="paginate-num" title="第'.$i.'页" href="'.$this->url($i).'">'.$i.'</a>';
		}
		if($this->current_page + $num_offset < $total_page_count){
			$html .= '<span class="paginate-dot"></span>';
		}

		if($this->current_page < $total_page_count){
			$html .= '<a class="paginate-next" title="下一页" href="'.$this->url($this->current_page + 1).'"></a>';
		}else{
			$html .= '<span class="paginate-next"></span>';
		}

		$html .= '<span class="paginate-total-info">共 '.$total_page_count.' 页</span>';
		if($this->config['allow_change_page_size']){
			$html .= '<span class="paginate-size-changer">每页 <select onchange="location.href=this.options[this.selectedIndex].getAttribute(\'data-url\');">';
			foreach($this->config['page_size_list'] as $size){
				$html .= '<option data-url="'.$this->url(1, $size == $this->page_size ? $size : null).'"'.($size == $this->page_size ? ' selected' : '').'>'.$size.'</option>';
			}
			$html .= '</select> 条</span>';
		}else{
			$html .= '<span class="paginate-size-changer">每页 '.$this->page_size.' 条</span>';
		}
		return "<div class=\"paginate paginate-total-$total_page_count\">$html</div>";
	}

	/**
	 * 生成分页 url
	 * @param number $page
	 * @param number|null $reset_page_size 是否重置每页大小
	 * @return string
	 * @throws \LFPhp\PLite\Exception\PLiteException
	 * @throws \LFPhp\PLite\Exception\RouterException
	 */
	private function url($page, $reset_page_size = null){
		$query = $_GET;
		$query[$this->config['page_key']] = $page;
		if($reset_page_size && $reset_page_size != $this->page_size){
			$query[$this->config['page_size_key']] = $reset_page_size;
		}
		return url(get_router(), $query);
	}

	public function setItemTotal($total){
		if($this->config['item_limit']){
			$this->item_total = min($this->config['item_limit'], $total);
		}else{
			$this->item_total = $total;
		}
	}

	public function getLimit(){
		return [
			($this->current_page - 1)*$this->page_size,
			$this->page_size,
		];
	}

	public function jsonSerialize(){
		return [
			'item_total'   => $this->item_total,
			'page_total'   => $this->item_total ? ceil($this->item_total/$this->page_size) : 0,
			'current_page' => $this->current_page,
			'page_size'    => $this->page_size,
		];
	}
}
