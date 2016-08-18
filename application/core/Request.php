<?php

namespace core;

/**
 * リクエスト情報を制御するcoreクラス<br>
 *
 * @package core
 */
class Request
{
    /**
     * リクエストのメソッドがPOSTであるかどうか判定する
     *
     * @return bool
     */
    public function isPost() : bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST'):
            return true;
        else:
            return false;
        endif;
    }

    /**
     * $nameで指定されたGETパラメタの値をバリデーションにかけてから返す
     *
     * @param string $name パラメタ名
     * @param string $default 値が設定されてなかった場合の返り値
     * @return string
     */
    public function getGetParam(string $name, string $default = ''): string
    {
        if (isset($_GET[$name])):
            return (string)filter_input(INPUT_GET, $name);
        else:
            return $default;
        endif;
    }

    /**
     * $nameで指定されたPOSTパラメタの値をバリデーションにかけてから返す
     *
     * @param string $name パラメタ名
     * @param string $default 値が設定されてなかった場合の返り値
     * @return string
     */
    public function getPostParam(string $name, string $default = ''): string
    {
        if (isset($_POST[$name])):
            return (string)filter_input(INPUT_POST, $name);
        else:
            return $default;
        endif;
    }

    /**
     * リクエストがHTTPSを用いてのものであるかを判定する
     *
     * @return bool
     */
    public function isSSL(): bool
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'):
            return true;
        else:
            return false;
        endif;
    }

    /**
     * リクエストを受けたサーバのホスト名を取得する
     *
     * リクエストヘッダにHostヘッダがない場合はApache側で設定されている値を用いる。
     *
     * @return string
     */
    public function getHost(): string
    {
        if (!empty($_SERVER['HTTP_HOST'])):
            return $_SERVER['HTTP_HOST'];
        else:
            return $_SERVER['SERVER_NAME'];
        endif;
    }

    /**
     * リクエストされたURIのうちホスト部以降の値を取得する
     *
     * @return string
     */
    public function getRequestUri(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * ベースURIを取得する<br>
     *
     * ベースURIはフロントコントローラに続くパスインフォを取り出すために使用される。
     * <pre>
     * ベースURIの抽出は以下の2つの場合にわけられる。
     * 1: フロントコントローラがURIに含まれる場合
     *  "/some/where/FrontController[/any]" => "/some/where/FrontController"
     * 2: フロントコントローラが省略された場合
     *  "/some/where[/any] => /some/where"
     * </pre>
     *
     * @return string
     */
    public function getBaseUri(): string
    {
        $script_name = $_SERVER['SCRIPT_NAME'];
        $request_uri = $this->getRequestUri();

        if (0 === strpos($request_uri, $script_name)):
            // 1: フロントコントローラがURLに含まれる場合
            return $script_name;
        elseif (0 === strpos($request_uri, dirname($script_name))):
            // 2: フロントコントローラが省略された場合
            return rtrim(dirname($script_name), '/');
        endif;

        return '';
    }

    /**
     * パスインフォを取得する<br>
     *
     * パスインフォはリクエストURIにおけるベースURIに続く部分である。
     * つまり、"/some/where[/FrontController][/path[/info[/...]]]"における、
     * "[/path[/info[/...]]]"の部分を指す。
     *
     * @return string
     */
    public function getPathInfo(): string
    {
        $base_url = $this->getBaseUri();
        $request_uri = $this->getRequestUri();

        // GETパラメータの除去
        if (false !== ($pos = strpos($request_uri, '?'))):
            $request_uri = substr($request_uri, 0, $pos);
        endif;

        // リクエストURIからベースURIを除いてパスインフォを取り出す
        $path_info = (string)substr($request_uri, strlen($base_url));

        return $path_info;
    }
}
