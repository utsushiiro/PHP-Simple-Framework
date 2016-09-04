<?php

namespace psf\core;
use psf\core\exceptions\RouteNotFoundException;

/**
 * ルーティングを制御するcoreクラス
 *
 * @package psf\core
 */
class Router
{
    /**
     * このRouterに登録されている{@link Route}集合
     *
     * @var Route[]
     */
    private $routes;

    /**
     * パスインフォパターンとそれに対応するルーティングパラメタの対応の集合
     *
     * {@link $routes} が {@link compileRoutes} により変換され作られる。
     * これを利用して {@link resolve} はパスインフォをルーティングパラメタに変換する。
     *
     * @var array
     */
    private $compiled_routes;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->routes = [];
    }

    /**
     * 引数で与えられたRouteを$routesに登録する
     *
     * @param Route $route
     * @param bool $replace
     * @return bool
     */
    public function addRoute(Route $route, bool $replace=true) : bool
    {
        if (array_key_exists($route->getPathInfo(), $this->routes)):
            if ($replace):
                $this->routes[$route->getPathInfo()] = $route->getRoutingParams();
                return true;
            else:
                return false;
            endif;
        else:
            $this->routes[$route->getPathInfo()] = $route->getRoutingParams();
            return true;
        endif;
    }

    /**
     * 引数で与えられたRouteを$routesから除外する
     *
     * @param Route $route
     * @return bool
     */
    public function removeRoute(Route $route) : bool
    {
        if (array_key_exists($route->getPathInfo(), $this->routes)):
            unset($this->routes[$route->getPathInfo()]);
            return true;
        else:
            return false;
        endif;
    }

    /**
     * $routesを元に$compiled_routesを作成する
     *
     * $routesに登録されているRouteの$path_infoに含まれる動的パラメタを正規表現に書き換え、
     * パスインフォパターンを作成する。そして、これを$keyとして連想配列$compiled_routesを作成する。
     * なお、$valueはRouteの$routing_paramsをそのまま使用する。
     */
    public function compileRoutes()
    {
        $this->compiled_routes = [];

        // $route から $compiled_routesを作成する
        foreach ($this->routes as $path_info => $routing_param):

            // $path_infoをセパレータ'/'でトークンに分離する
            $tokens = explode('/', $path_info);

            // トークンが':'で始まる場合(動的パラメタの場合)、正規表現に置き換える
            foreach ($tokens as &$token) :
                if (strpos($token, ':') === 0):
                    $name = substr($token, 1);
                    $token = '(?P<' . $name . '>[^/]+)';
                endif;
            endforeach;

            // 分離したトークンを再結合する
            // PCRE正規表現を使用するため'#'で囲む
            $pattern = '#\A'. implode('/', $tokens) . '\z#u';
            $this->compiled_routes[$pattern] = $routing_param;
        endforeach;
    }

    /**
     * パスインフォのマッチングを行い、ルーティングパラメタを返す
     *
     * 引数で与えられたパスインフォと$compiled_routesのパスインフォパターンのマッチングを行い、
     * 対応するルーティングパラメタにマッチングにより得られた動的パラメタの情報を加えた配列を返す。
     * マッチするパスインフォパターンがなかった場合は {@link RouteNotFoundException} を送出する。
     *
     * @param string $path_info
     * @return array
     * @throws RouteNotFoundException
     */
    public function resolve(string $path_info)
    {
        // $path_infoの先頭に「/」がない場合は付ける
        if (substr($path_info, 0, 1) !== '/'):
            $path_info = '/' . $path_info;
        endif;
        
        foreach ($this->compiled_routes as $pattern => $params):
            $res = preg_match($pattern, $path_info, $matches);
            if ($res === 1):
                    $params = array_merge($params, $matches);
                    return $params;
            endif;
        endforeach;

        throw new RouteNotFoundException("There is no route that matches '$path_info'. ");
    }

    /**
     * [debug] $routesと$compiled_routesのダンプ
     */
    public function dump_routes()
    {
        echo '---------- $routes ---------', PHP_EOL;
        var_dump($this->routes);
        echo '----- $compiled_routes -----', PHP_EOL;
        var_dump($this->compiled_routes);
    }
}