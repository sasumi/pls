<?php

use function LFPhp\Pls\mixing_template;

return [
	'file_structure' => [
		'$PROJECT_DIR/config',
		'$PROJECT_DIR/log',
		'$PROJECT_DIR/public/js',
		'$PROJECT_DIR/public/css',
		'$PROJECT_DIR/public/img',
		'$PROJECT_DIR/public/file',
		'$PROJECT_DIR/script',
		'$PROJECT_DIR/src/Http/Controller',
		'$PROJECT_DIR/src/Http/Exception',
		'$PROJECT_DIR/src/ORM/$PROJECT_NAME/Model',
		'$PROJECT_DIR/src/ORM/$PROJECT_NAME/Table',
		'$PROJECT_DIR/src/Business',
		'$PROJECT_DIR/src/test',
		'$PROJECT_DIR/src/vendor',
	],
	'file_template'  => [
		'$PROJECT_DIR/config/routes.inc.php' => function($project){
			return mixing_template('router.inc.php', compact('project'));
		},
	],
];
