<?php
require_once 'psf/core/ClassLoader.php';

// 名前空間の設定
$loader = new \psf\core\ClassLoader();
$loader->addFrameworkNameSpace('psf\\core');
$loader->addFrameworkNameSpace('psf\\core\\exceptions');
$loader->addFrameworkNameSpace('psf\\lib');
$loader->addFrameworkNameSpace('app\\controller');
$loader->addFrameworkNameSpace('app\\model');

// エラーハンドラの設定(エラーをすべて例外送出に変換)
set_error_handler(function ($errno, $errstr, $errfile, $errline ) {
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});