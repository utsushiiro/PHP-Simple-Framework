<?php

namespace core;

/**
 * パスインフォとそれに対応するルール(呼び出すコントローラ、アクション)を定義するcoreクラス
 *
 * '/user/:id/edit' => array('controller' => 'user', 'action' => 'edit') という対応の場合、
 * '/user/:id/edit' は $path_info に, array('controller' => 'user', ...) は $routing_params に格納される
 *
 * @package core
 */
class Route
{
    /**
     * パスインフォ(動的パラメタ含む)<br>
     *
     * 動的パラメタとは/user/:id 等の ":"で始まる文字列を指す。
     *
     * @var string
     */
    private $path_info;

    /**
     * パスインフォに対応するコントローラとアクション等の情報<br>
     *
     * @var string[]
     */
    private $routing_params;

    /**
     * Route constructor.
     *
     * @param string $path_info
     * @param string[] $routing_params
     */
    public function __construct(string $path_info, array $routing_params)
    {
        $this->path_info = $path_info;
        $this->routing_params = $routing_params;
    }

    /**
     * @return string
     */
    public function getPathInfo(): string
    {
        return $this->path_info;
    }

    /**
     * @return string[]
     */
    public function getRoutingParams(): array
    {
        return $this->routing_params;
    }

    /**
     * Routeオブジェクトの等価判定
     *
     * $path_infoが同一であるRouteオブジェクトを等価であると定める。
     *
     * @param Route $route 比較するRoute
     * @return bool
     */
    public function equals(Route $route): bool
    {
        return $this->path_info === $route->path_info;
    }
}
