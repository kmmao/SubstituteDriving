<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitad3a9aa249d827d7da494fc28d2e1cee
{
    public static $prefixLengthsPsr4 = array (
        't' => 
        array (
            'think\\composer\\' => 15,
        ),
        'a' => 
        array (
            'app\\' => 4,
        ),
        'G' => 
        array (
            'GatewayClient\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'think\\composer\\' => 
        array (
            0 => __DIR__ . '/..' . '/topthink/think-installer/src',
        ),
        'app\\' => 
        array (
            0 => __DIR__ . '/../..' . '/application',
        ),
        'GatewayClient\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/gatewayclient',
        ),
    );

    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'PHPExcel' => 
            array (
                0 => __DIR__ . '/..' . '/phpoffice/phpexcel/Classes',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitad3a9aa249d827d7da494fc28d2e1cee::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitad3a9aa249d827d7da494fc28d2e1cee::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitad3a9aa249d827d7da494fc28d2e1cee::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
