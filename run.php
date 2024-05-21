<?php
use LFPhp\Pls\ProjectBuilder;

$project_vendor_dir = dirname(__DIR__, 2);
$project_root = dirname(__DIR__,3);
include_once $project_vendor_dir.'/autoload.php';

ProjectBuilder::$app_root = $project_root;
ProjectBuilder::start();
