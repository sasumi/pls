<?php

namespace LFPhp\Pls\command;

use LFPhp\Pls\ProjectBuilder;

class CmdBase extends Cmd {

	public function getCmd(){
		return 'base';
	}

	public function getDescription(){
		return 'Initialize project basic directory structure';
	}

	public function run(){
		ProjectBuilder::initFile('tag-base');
	}
}
