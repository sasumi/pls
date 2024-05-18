<?php

namespace LFPhp\Pls;

use Composer\Installer\InstallerInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

class PlsInstaller implements InstallerInterface{
	public function install(InstalledRepositoryInterface $repo, PackageInterface $package){
	}

	public function supports(string $packageType){
		// TODO: Implement supports() method.
	}

	public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package){
		// TODO: Implement isInstalled() method.
	}

	public function download(PackageInterface $package, PackageInterface $prevPackage = null){
		// TODO: Implement download() method.
	}

	public function prepare(string $type, PackageInterface $package, PackageInterface $prevPackage = null){
		// TODO: Implement prepare() method.
	}

	public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target){
		// TODO: Implement update() method.
	}

	public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package){
		// TODO: Implement uninstall() method.
	}

	public function cleanup(string $type, PackageInterface $package, PackageInterface $prevPackage = null){
		// TODO: Implement cleanup() method.
	}

	public function getInstallPath(PackageInterface $package){
		// TODO: Implement getInstallPath() method.
	}
}
