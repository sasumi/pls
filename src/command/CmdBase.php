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
		ProjectBuilder::addGitIgnore([
			'/.runtime',
			'/log',
		]);
		$project_info = ProjectBuilder::getProjectInfo();
		ProjectBuilder::addConfigItems($project_info['app_root'].'/config/routes.inc.php', [
			"'' => {$project_info['app_namespace']}\Http\Controller\Index::class.'@index',"
		]);
	}
}
