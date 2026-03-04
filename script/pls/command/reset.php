<?php
namespace LFPhp\Pls;

use LFPhp\Logger\Logger;

return [
	'Reset project composer info',
	function(){
		$project_name = pls_console_read_required('Please input project name, use [lfphp/pls] as default: ', true);
		pls_update_project_info($project_name);
		Logger::info('project name updated: ', $project_name);
	},
];
