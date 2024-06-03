<?php

namespace LFPhp\Pls\command;

use LFPhp\Pls\ProjectBuilder;

class CmdCrud extends Cmd {

	public function getCmd(){
		return 'crud';
	}

	public function getDescription(){
		return 'Generate CRUD(Create, Read, Update, Delete) operation function';
	}

	public function run(){

		ProjectBuilder::initFile('tag-crud');
	}
}
