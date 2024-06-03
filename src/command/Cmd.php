<?php

namespace LFPhp\Pls\command;

abstract class Cmd {
	abstract public function getCmd();

	public function getDescription(){
		return '';
	}

	abstract public function run();

	final public static function runCmd($cmd){
		$commands = self::getAllCmd();
		$class = $commands[$cmd]['class'];

//		dump($cmd, $class);
		/** @var self $ins */
		$ins = new $class();
		$ins->run();
	}

	/**
	 * @return array
	 */
	final public static function getAllCmd(){
		$commands = [];
		$cmd_indexed = [
			CmdHelp::class,
			CmdInit::class,
			CmdEnvConfirm::class,
			CmdBase::class,
			CmdDatabase::class,
			CmdOrm::class,
			CmdCrud::class,
			CmdFront::class,
		];
		foreach($cmd_indexed as $class){
			/** @var self $ins */
			$ins = new $class;
			$cmd = $ins->getCmd();
			$commands[$cmd] = [
				'cmd'         => $ins->getCmd(),
				'class'       => $class,
				'description' => $ins->getDescription(),
			];
		}
		return $commands;
	}
}
