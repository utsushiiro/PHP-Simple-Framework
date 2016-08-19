<?php

require_once "../bootstrap.php";

$router = new \core\Router();

// ルーティングパラメタのindexについて、主要なもの(コントローラ等)は
// 文字列ではなく定数(等)で指定できるようにすべきかも
$route1 = new \core\Route(
    '/',
    array('controller' => 'home', 'action' => 'index')
);
$route2 = new \core\Route(
    '/user/:id/edit',
    array(
        'controller' => 'user',
        'action' => 'edit'
    )
);
$route3 = new \core\Route(
    '/',
    array('controller' => 'foo', 'action' => 'bar')
);


// addRoute(), compileRoutes()の動作確認
$router->addRoute($route1);
$router->addRoute($route2);
$router->addRoute($route3, false);
$router->compileRoutes();
$router->dump_routes();

// $route1のresolve()の動作確認
$path_info = '/';
$routing_params = $router->resolve($path_info);
echo '----- $route1のresolve() ------', PHP_EOL;
var_dump($routing_params);

// $route2のresolve()の動作確認
echo '----- $route2のresolve() ------', PHP_EOL;
$path_info = '/user/2/edit';
$routing_params = $router->resolve($path_info);
var_dump($routing_params);

// $route4のresolve()の動作確認
echo '----- $route4のresolve() ------', PHP_EOL;
$route4 = new \core\Route(
    '/user/:id/edit/:article',
    array(
        'controller' => 'user',
        'action' => 'edit'
    )
);
$router->addRoute($route4);
$router->compileRoutes();
$router->dump_routes();
$path_info = '/user/114/edit/514';
$routing_params = $router->resolve($path_info);
var_dump($routing_params);

// 一致するパスインフォパターンがない場合の例外送出テスト
echo '----- resolve()からのRouteNotFoundException ------', PHP_EOL;
$path_info = '/unknown';
$routing_params = $router->resolve($path_info);
