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
use function LFPhp\Func\http_send_cors;
use function LFPhp\Func\instanceof_list;
use function LFPhp\PLite\get_app_env;
use function LFPhp\PLite\bind_json_response_handler;
use function LFPhp\PLite\start_web;
use const LFPhp\PLite\ENV_LOCAL;
use const LFPhp\PLite\EVENT_APP_BEFORE_EXEC;
use const LFPhp\PLite\EVENT_APP_EXCEPTION;
use const LFPhp\PLite\EVENT_APP_EXECUTED;
use const LFPhp\PLite\EVENT_APP_FINISHED;
use const LFPhp\PLite\EVENT_APP_START;
use const LFPhp\PLite\EVENT_ROUTER_HIT;

include_once __DIR__.'/../bootstrap.php';

start_web(function(){
	ini_set('error_log', PLITE_APP_ROOT . '/log/www.error.log');
	DBDriver::setLogger(Logger::instance(DBDriver::class));
	Logger::registerWhileGlobal(LoggerLevel::WARNING, new FileOutput(PLITE_APP_ROOT . '/log/www.error.log'), LoggerLevel::DEBUG);

	if (get_app_env() === ENV_LOCAL) {
		Logger::registerWhileGlobal(LoggerLevel::DEBUG, new FileOutput(PLITE_APP_ROOT . '/log/www.runtime.log'), LoggerLevel::DEBUG);
		foreach ([EVENT_APP_START, EVENT_APP_BEFORE_EXEC, EVENT_APP_EXECUTED, EVENT_APP_FINISHED, EVENT_ROUTER_HIT] as $ev) {
			event_register($ev, function (...$args) use ($ev) {
				$time_offset = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000);
				Logger::instance('🎯PLITE ' . "[+{$time_offset}ms]")->debug($ev, $ev !== EVENT_APP_EXECUTED ? array_clean_null($args) : '');
			});
		}
	}

	bind_json_response_handler(function ($rsp) {
		if ($rsp instanceof Exception) {
			return MessageException::fromException($rsp, false)->toArray();
		}
		return MessageException::successData($rsp);
	});

	event_register(EVENT_APP_EXCEPTION, function (Exception $exception) {
		if (!instanceof_list($exception, [MessageException::class, RouterException::class, NotFoundException::class])) {
			$time_offset = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000);
			Logger::instance('🎯PLITE ' . "[+{$time_offset}ms]")->exception($exception);
		}
	});

	//设置CORS信息，预检和正式请求都必须返回CORS头信息
	//阶段	必须返回的响应头
	//预检请求（OPTIONS）	Access-Control-Allow-Origin
	//Access-Control-Allow-Methods
	//Access-Control-Allow-Headers
	//Access-Control-Allow-Credentials（可选）
	//正式请求（GET、POST 等）	Access-Control-Allow-Origin
	//Access-Control-Allow-Credentials（如果使用了 withCredentials: true）
	if($_SERVER['HTTP_ORIGIN']){
		http_send_cors(['abc.com','*']);
	}
});
