<?php
namespace LFPhp\Pls;

return [
	'Initialize project basic directory structure',
	function(){
		pls_init_file('tag-base');
		$project_info = pls_get_project_info();
		pls_add_config_items($project_info['app_root'].'/config/routes.inc.php', [
			"'' => {$project_info['app_namespace']}\Http\Controller\Index::class.'@index',",
		]);
	},
];
