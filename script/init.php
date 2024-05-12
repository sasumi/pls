<?php

use function LFPhp\Func\mkdir_batch;
use function LFPhp\Pls\get_pls_config;

include dirname(__DIR__).'/vendor/autoload.php';
$project_dir = PLS_ROOT.'/test/prj1';
$file_struct = get_pls_config('file/file_structure');
$file_struct = str_replace('$PROJECT_DIR', $project_dir, $file_struct);
mkdir_batch($file_struct);
echo 'Project file structure created:', PHP_EOL;
foreach($file_struct as $f){
	echo realpath($f), PHP_EOL;
}
echo "success";
