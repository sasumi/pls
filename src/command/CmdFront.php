<?php

namespace LFPhp\Pls\command;

use LFPhp\Pls\ProjectBuilder;

class CmdFront extends Cmd {

	public function getCmd(){
		return 'frontend';
	}

	public function getDescription(){
		return 'Integrated WebCom component';
	}

	public function run(){
		ProjectBuilder::initFile('tag-frontend');
	}
}
