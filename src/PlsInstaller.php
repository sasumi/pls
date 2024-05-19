<?php

namespace LFPhp\Pls;


use Composer\Composer;
use Composer\Installer\InstallerInterface;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Repository\InstalledRepositoryInterface;

class PlsInstaller implements PluginInterface, InstallerInterface {
	public function activate(Composer $composer, IOInterface $io){
		// TODO: Implement activate() method.
	}

	public function deactivate(Composer $composer, IOInterface $io){
		// TODO: Implement deactivate() method.
	}

	public function uninstall(Composer $composer, IOInterface $io){
		// TODO: Implement uninstall() method.
	}

	public function supports(string $packageType){
		// TODO: Implement supports() method.
	}

	public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package){
		return true;
	}

	public function download(PackageInterface $package, ?PackageInterface $prevPackage = null){
		// TODO: Implement download() method.
	}

	public function prepare(string $type, PackageInterface $package, ?PackageInterface $prevPackage = null){
		// TODO: Implement prepare() method.
	}

	public function install(InstalledRepositoryInterface $repo, PackageInterface $package){
		// TODO: Implement install() method.
	}

	public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target){
		// TODO: Implement update() method.
	}

	public function cleanup(string $type, PackageInterface $package, ?PackageInterface $prevPackage = null){
		// TODO: Implement cleanup() method.
	}

	public function getInstallPath(PackageInterface $package){
		// TODO: Implement getInstallPath() method.
	}
}
