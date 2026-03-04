<?php

namespace {app_namespace}\Http;

abstract class Controller {
	public function __construct(){
		if(PHP_SAPI !== 'cli'){
			$this->onAuthorize();
		}
		Paginate::setDefaultPageSize(20);
	}
	abstract public function onAuthorize();
}
