<?php
namespace {app_namespace};

use Exception;
use LFPhp\Logger\Logger;
use LFPhp\Logger\LoggerLevel;
use LFPhp\Logger\Output\FileOutput;
use LFPhp\PLite\Exception\MessageException;
use LFPhp\PLite\Exception\RouterException;
use LFPhp\PORM\DB\DBDriver;
use LFPhp\PORM\Exception\NotFoundException;
use function LFPhp\Func\array_clean_null;
use function LFPhp\Func\event_register;
use function LFPhp\Func\instanceof_list;
use function LFPhp\PLite\default_exception_handle;
use function LFPhp\PLite\default_response_handle;
use function LFPhp\PLite\start_web;
use const LFPhp\PLite\EVENT_APP_BEFORE_EXEC;
use const LFPhp\PLite\EVENT_APP_EXCEPTION;
use const LFPhp\PLite\EVENT_APP_EXECUTED;
use const LFPhp\PLite\EVENT_APP_FINISHED;
use const LFPhp\PLite\EVENT_APP_START;
use const LFPhp\PLite\EVENT_ROUTER_HIT;

include_once __DIR__.'/../bootstrap.php';

start_web(function(){
	//set application log
	ini_set('error_log', PLITE_APP_ROOT.'/log/www.error.log');
	DBDriver::setLogger(Logger::instance(DBDriver::class));
	Logger::registerWhileGlobal(LoggerLevel::DEBUG, new FileOutput(PLITE_APP_ROOT.'/log/www.runtime.log'), LoggerLevel::DEBUG);
	Logger::registerWhileGlobal(LoggerLevel::WARNING, new FileOutput(PLITE_APP_ROOT.'/log/www.error.log'), LoggerLevel::DEBUG);

	//performance debug
	foreach([EVENT_APP_START, EVENT_APP_BEFORE_EXEC, EVENT_APP_EXECUTED, EVENT_APP_FINISHED, EVENT_ROUTER_HIT] as $ev){
		event_register($ev, function(...$args) use ($ev){
			$time_offset = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'])*1000);
			Logger::instance('🎯PLITE '."[+{$time_offset}ms]")->debug($ev, $ev !== EVENT_APP_EXECUTED ? array_clean_null($args) : '');
		});
	}

	//application response logic
	event_register(EVENT_APP_EXECUTED, function($rsp, $ctrl, $act){
		default_response_handle(['data' => $rsp, 'message' => '操作成功'], $ctrl, $act);
	});
	event_register(EVENT_APP_EXCEPTION, function(Exception $e){
		$normalExceptions = [MessageException::class, RouterException::class, NotFoundException::class];
		$time_offset = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'])*1000);
		!instanceof_list($e, $normalExceptions) && Logger::instance('🎯PLITE '."[+{$time_offset}ms]")->exception($e);
		default_exception_handle($e, $normalExceptions);
	});
	session_start();
});
