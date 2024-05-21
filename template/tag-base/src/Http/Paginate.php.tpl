<?php

namespace {app_namespace}\Http;

use JsonSerializable;
use LFPhp\PORM\Misc\PaginateInterface;

class Paginate implements PaginateInterface, JsonSerializable {
	private $item_total;
	private $current_page;
	private static $default_page_size;

	private $config = [
		'page_key'  => 'page',
		'page_size_key' => 'page_size',
		'page_size' => 20
	];

	private function __construct($config){
		if(!$config['page_size'] && self::$default_page_size){
			$config['page_size'] = self::$default_page_size;
		}
		$this->config = array_merge($this->config, $config);
		$page_key = $this->config['page_key'];
		$page_size_key = $this->config['page_size_key'];
		$this->current_page = max(isset($_REQUEST[$page_key]) ? intval($_REQUEST[$page_key]) : 1, 1);
		$this->config['page_size'] = $_REQUEST[$page_size_key] ? intval($_REQUEST[$page_size_key]) : $this->config['page_size'];
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
		$total_page_count = ceil($this->item_total/$this->config['page_size']);
		$html .= '<span class="paginate-total-info">共'.$this->item_total.'条</span>';
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
		for($i=min($num_offset, $this->current_page-1); $i>0; $i--){
			$p = $this->current_page-$i;
			$html .= '<a class="paginate-num" title="第'.$p.'页" href="'.$this->url($p).'">'.$p.'</a>';
		}
		$html .= '<span class="paginate-num paginate-current">'.$this->current_page.'</span>';

		//后置部分
		for($i = $this->current_page+1; $i<=min($total_page_count, $this->current_page+$num_offset); $i++){
			$html .= '<a class="paginate-num" title="第'.$i.'页" href="'.$this->url($i).'">'.$i.'</a>';
		}
		if($this->current_page + $num_offset <$total_page_count){
			$html .= '<span class="paginate-dot"></span>';
		}

		if($this->current_page < $total_page_count){
			$html .= '<a class="paginate-next" title="下一页" href="'.$this->url($this->current_page + 1).'"></a>';
		}else{
			$html .= '<span class="paginate-next"></span>';
		}
		$options = [10, 20, 50, 100, 200, 500, 1000, 2000];
		$html .= '<span class="paginate-size-changer">每页 <select onchange="location.href=this.options[this.selectedIndex].getAttribute(\'data-url\');">';
		foreach($options as $size){
			$html .= '<option data-url="'.$this->url(1, $size).'"'.($size == $this->config['page_size'] ? ' selected':'').'>'.$size.'</option>';
		}
		$html .= '</select> 条</span>';
		return "<div class=\"paginate paginate-total-$total_page_count\">$html</div>";
	}

	private function url($page, $page_size = null){
		$_REQUEST[$this->config['page_key']] = $page;
		$_REQUEST[$this->config['page_size_key']] = $page_size ?: $this->config['page_size'];
		return '?'.http_build_query($_REQUEST);
	}

	public function setItemTotal($total){
		$this->item_total = $total;
	}

	public function getLimit(){
		return [
			($this->current_page - 1)*$this->config['page_size'],
			$this->config['page_size'],
		];
	}

	public function jsonSerialize(){
		return [
			'item_total'   => $this->item_total,
			'page_total'   => $this->item_total ? ceil($this->item_total/$this->config['page_size']) : 0,
			'current_page' => $this->current_page,
			'page_size'    => $this->config['page_size'],
		];
	}
}
