<?php
namespace {app_namespace};

use Exception;
use LFPhp\Logger\Logger;
use LFPhp\Logger\LoggerLevel;
use LFPhp\Logger\Output\FileOutput;
use LFPhp\PLite\Exception\MessageException;
use function LFPhp\Func\array_clear_null;
use function LFPhp\Func\event_register;
use function LFPhp\PLite\start_web;
use const LFPhp\PLite\EVENT_APP_BEFORE_EXEC;
use const LFPhp\PLite\EVENT_APP_EXCEPTION;
use const LFPhp\PLite\EVENT_APP_EXECUTED;
use const LFPhp\PLite\EVENT_APP_FINISHED;
use const LFPhp\PLite\EVENT_APP_START;
use const LFPhp\PLite\EVENT_ROUTER_HIT;

try{
	include_once __DIR__.'/../bootstrap.php';
	if(!session_start()){
		throw new Exception('Session start failure');
	}

	Logger::registerGlobal(new FileOutput(PLITE_APP_ROOT.'/log/www.runtime.log'), LoggerLevel::DEBUG);
	Logger::registerGlobal(new FileOutput(PLITE_APP_ROOT.'/log/www.error.log'), LoggerLevel::WARNING);

	foreach([EVENT_APP_START, EVENT_APP_BEFORE_EXEC, EVENT_APP_EXECUTED, EVENT_APP_FINISHED, EVENT_ROUTER_HIT,] as $ev){
		event_register($ev, function(...$args) use ($ev){
			$time_offset = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'])*1000);
			Logger::instance("[+{$time_offset}ms] ".'🎯PLITE')->info($ev, $ev !== EVENT_APP_EXECUTED ? array_clear_null($args) : '');
		});
	}
	event_register(EVENT_APP_EXCEPTION, function(Exception $ex){
		Logger::instance('🎯 PLITE')->error(get_class($ex),
			"\n[Exception]\n\t", $ex->getMessage(),
			"\n[Location]\n\t", $ex->getFile().' #'.$ex->getLine(),
			"\n[Trace]\n\t", str_replace("\n", "\n\t", trim($ex->getTraceAsString())));
	});
	event_register(EVENT_APP_EXECUTED, '\LFPhp\PLite\default_response_handle');
	event_register(EVENT_APP_EXCEPTION, '\LFPhp\PLite\default_exception_handle');
	start_web();
}catch(Exception $e){
	if($e instanceof MessageException){
		return;
	}
	error_log($e->getMessage());
}
