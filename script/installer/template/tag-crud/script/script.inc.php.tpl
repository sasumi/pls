<?php

use LFPhp\Logger\Logger;
use LFPhp\Logger\LoggerLevel;
use LFPhp\Logger\Output\ConsoleOutput;
use LFPhp\Logger\Output\FileOutput;

include_once __DIR__.'/../bootstrap.php';

Logger::registerGlobal(new ConsoleOutput(), LoggerLevel::DEBUG);
Logger::registerGlobal(new FileOutput(PLITE_APP_ROOT.'/log/script.error.log'), LoggerLevel::WARNING);
