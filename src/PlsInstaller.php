<?php

namespace LFPhp\Pls;


use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class PlsInstaller implements PluginInterface {
	public function activate(Composer $composer, IOInterface $io){
		// TODO: Implement activate() method.
	}

	public function deactivate(Composer $composer, IOInterface $io){
		// TODO: Implement deactivate() method.
	}

	public function uninstall(Composer $composer, IOInterface $io){
		// TODO: Implement uninstall() method.
	}
}
