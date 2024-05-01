<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd377d30c66f9f404fb89b7146b089b2e
{
    public static $files = array (
        'bbf50544e76e6a7818a02a28d5e449fa' => __DIR__ . '/..' . '/lfphp/func/src/array.php',
        'e743b7ab97548186e9de00316b6d4405' => __DIR__ . '/..' . '/lfphp/func/src/color.php',
        '6a6c6441f52e3238d94df088bef5e762' => __DIR__ . '/..' . '/lfphp/func/src/cron.php',
        'b663746832c5fde6375865dfbc517e07' => __DIR__ . '/..' . '/lfphp/func/src/csp.php',
        'd96776328d9461fccc9b013cbeefa647' => __DIR__ . '/..' . '/lfphp/func/src/curl.php',
        '9655d52f0fb09ad452fa8547b3f83cc2' => __DIR__ . '/..' . '/lfphp/func/src/db.php',
        '6c678bc5cec0a6847cd164de6e1b74fa' => __DIR__ . '/..' . '/lfphp/func/src/env.php',
        '2b6a149990b2a7b068272d1f1fb32ba6' => __DIR__ . '/..' . '/lfphp/func/src/file.php',
        '8a461ae6b8c1f2e09d35494276a354e6' => __DIR__ . '/..' . '/lfphp/func/src/font.php',
        'c7a98d573681945c704327c4f54a29ff' => __DIR__ . '/..' . '/lfphp/func/src/html.php',
        '0f569c05b8a56915b23ecac48714ba16' => __DIR__ . '/..' . '/lfphp/func/src/http.php',
        '35fcc14f3a20915ff8d096ff71d70f4b' => __DIR__ . '/..' . '/lfphp/func/src/session.php',
        '96525a81412da1dc6bc452df41cca035' => __DIR__ . '/..' . '/lfphp/func/src/sheet.php',
        '9fd272e63096bdfab6672831e2fc7fd4' => __DIR__ . '/..' . '/lfphp/func/src/string.php',
        '38d93b268ce45d4c48e6ed3d8634fb00' => __DIR__ . '/..' . '/lfphp/func/src/time.php',
        '852afe357795df2e7329c111d2e8b564' => __DIR__ . '/..' . '/lfphp/func/src/util.php',
        'c3c106f3aac32d5bbdf38f4dc7302aed' => __DIR__ . '/..' . '/lfphp/plite/src/app.php',
        '071990caedc05aeef2f061b42112188b' => __DIR__ . '/..' . '/lfphp/plite/src/config.php',
        '0d0c4b096e0f4f0c8bc1dba4f71c079a' => __DIR__ . '/..' . '/lfphp/plite/src/defines.php',
        'f5aea0560470b9a708e87ec2e1d1aeb4' => __DIR__ . '/..' . '/lfphp/plite/src/event.php',
        '94295dad232e19183e6562b2fc414009' => __DIR__ . '/..' . '/lfphp/plite/src/page.php',
        '184e0050df6a067784c7805ddbfe9649' => __DIR__ . '/..' . '/lfphp/plite/src/router.php',
    );

    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'Lfphp\\Pls\\' => 10,
            'LFPhp\\PLite\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Lfphp\\Pls\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'LFPhp\\PLite\\' => 
        array (
            0 => __DIR__ . '/..' . '/lfphp/plite/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd377d30c66f9f404fb89b7146b089b2e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd377d30c66f9f404fb89b7146b089b2e::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitd377d30c66f9f404fb89b7146b089b2e::$classMap;

        }, null, ClassLoader::class);
    }
}
