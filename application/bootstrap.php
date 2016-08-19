<?php
require_once 'core/ClassLoader.php';

// 名前空間の設定
$loader = new \core\ClassLoader();
$loader->addFrameworkNameSpace('core');
$loader->addFrameworkNameSpace('core\\exceptions');
$loader->addFrameworkNameSpace('controller');
$loader->addFrameworkNameSpace('model');

// エラーハンドラの設定(エラーをすべて例外送出に変換)
set_error_handler(function ($errno, $errstr, $errfile, $errline ) {
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});