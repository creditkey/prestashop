<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit455e2e38aca6ab16aa479ee7aceb0e1c
{
    public static $prefixLengthsPsr4 = array (
        'K' => 
        array (
            'Kick\\Creditkey\\' => 15,
        ),
        'C' => 
        array (
            'CreditKey\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Kick\\Creditkey\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'CreditKey\\' => 
        array (
            0 => __DIR__ . '/..' . '/creditkey/creditkey-php/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit455e2e38aca6ab16aa479ee7aceb0e1c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit455e2e38aca6ab16aa479ee7aceb0e1c::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit455e2e38aca6ab16aa479ee7aceb0e1c::$classMap;

        }, null, ClassLoader::class);
    }
}
