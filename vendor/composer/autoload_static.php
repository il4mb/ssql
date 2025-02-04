<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite3aeac5d7b406864cbb36825a54b7906
{
    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'Il4mb\\SSQL\\' => 11,
        ),
        'D' => 
        array (
            'Doctrine\\SqlFormatter\\' => 22,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Il4mb\\SSQL\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Doctrine\\SqlFormatter\\' => 
        array (
            0 => __DIR__ . '/..' . '/doctrine/sql-formatter/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite3aeac5d7b406864cbb36825a54b7906::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite3aeac5d7b406864cbb36825a54b7906::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInite3aeac5d7b406864cbb36825a54b7906::$classMap;

        }, null, ClassLoader::class);
    }
}
