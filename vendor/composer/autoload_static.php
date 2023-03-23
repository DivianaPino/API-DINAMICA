<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit7540714a8801ebf2cf2489ea5727ef63
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit7540714a8801ebf2cf2489ea5727ef63::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit7540714a8801ebf2cf2489ea5727ef63::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit7540714a8801ebf2cf2489ea5727ef63::$classMap;

        }, null, ClassLoader::class);
    }
}
