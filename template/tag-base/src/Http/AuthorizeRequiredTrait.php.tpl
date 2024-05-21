<?php

namespace {app_namespace}\Http;

use {app_namespace}\Http\Controller\Auth;

trait AuthorizeRequiredTrait {
	public function onAuthorize(){
		//todo
		return true;
	}
}
