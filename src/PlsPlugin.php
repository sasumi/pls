<?php

namespace LFPhp\Pls;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class PlsPlugin implements PluginInterface, EventSubscriberInterface {

	public function activate(Composer $composer, IOInterface $io){
		$installer = new PlsInstaller($io, $composer);
		$composer->getInstallationManager()->addInstaller($installer);
	}

	public function deactivate(Composer $composer, IOInterface $io){
		// TODO: Implement deactivate() method.
	}

	public function uninstall(Composer $composer, IOInterface $io){
		// TODO: Implement uninstall() method.
	}

	public static function cleanUp(){

	}

	public static function getSubscribedEvents(){
		return [
			'post-autoload-dump' => 'cleanUp',
		];
	}
}
