<?php

namespace LFPhp\Pls\command;
use LFPhp\Pls\ProjectBuilder;

class CmdIndex extends Cmd {

	public function getCmd(){
		return 'index';
	}

	public function getDescription(){
		return 'Generate Index controller & page';
	}

	public function run(){
		ProjectBuilder::initFile('tag-index');
	}
}
