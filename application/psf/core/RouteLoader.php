<?php

namespace psf\core;

/**
 * ルーティング情報(routes.ini)を読み取りルータを作成するcoreクラス
 *
 * configディレクト
 * @package psf\core
 */
class RouteLoader
{
    /**
     * ルーティング情報(routes.ini)に記述されたルートを登録したRouterを作成する
     */
    public static function getRouter(): Router
    {
        $routes_ini_filename = ConfigLoader::get('CORE', 'CONFIGS_ROOT') . DIRECTORY_SEPARATOR . 'routes.ini';
        $routes_ini = parse_ini_file($routes_ini_filename, true, INI_SCANNER_RAW);

        if ($routes_ini === false):
            throw new \RuntimeException("Failed to parse ${routes_ini_filename}");
        endif;

        $router = new Router();

        foreach ($routes_ini as $path_info => $routing_params):
            $route = new Route($path_info, $routing_params);
            $router->addRoute($route);
        endforeach;

        $router->compileRoutes();

        return $router;
    }
}