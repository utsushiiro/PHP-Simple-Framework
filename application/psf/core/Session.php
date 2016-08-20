<?php

namespace psf\core;

/**
 * セッションを管理するcoreクラス
 *
 * このクラスは一回のリクエストに一度だけインスタンス化される。<br>
 * セッションは最初に{@link Session::getInstance}を呼び出した時点で開始される。
 *
 * TODO: 認証を担当するクラスを作成して認証処理をそちらに移す
 *
 * @package psf\core
 */
class Session
{
    /**
     * @var Session
     */
    private static $instance = null;

    /**
     * Session constructor.
     *
     * インスタンス生成時にセッションを開始する。
     */
    private function __construct()
    {
        session_start();
    }

    /**
     * Session destructor
     *
     * Sessionオブジェクト破棄時にセッションも破棄する。
     */
    function __destruct()
    {
        $this->destroy();
    }

    /**
     * Sessionオブジェクトを返す
     *
     * Sessionオブジェクトがまだ作成されていない場合は、作成した後これを返す。
     * また、作成するタイミングでセッションは開始される。
     *
     * @return Session
     */
    public static function getInstance()
    {
        if (self::$instance === null):
            self::$instance = new Session();
        endif;

        return self::$instance;
    }

    /**
     * @param string $name
     * @param null $default
     * @return string
     */
    public function getValue(string $name, $default = null) : string
    {
        if (isset($_SESSION[$name])):
            return $_SESSION[$name];
        endif;

        return $default;
    }

    /**
     * @param string $name
     * @param $value
     */
    public function setValue(string $name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * セッション変数から index が $name である要素を消去する
     *
     * @param string $name
     */
    public function removeValue(string $name)
    {
        unset($_SESSION[$name]);
    }

    /**
     * セッション変数を空にする
     */
    public function removeAllValue()
    {
        $_SESSION = [];
    }

    /**
     * セッションを破棄する
     */
    public function destroy()
    {
        $_SESSION = [];
        session_destroy();
    }

    /**
     * 認証状態を$statusに変更後、新たなセッションIDを発行し乗り換える
     *
     * @param bool $status 認証状態
     */
    public function setAuthenticated(bool $status)
    {
        $this->setValue('_authenticated', $status);
        session_regenerate_id(true);
    }

    /**
     * 現在の認証状態を返す
     *
     * @return string
     */
    public function isAuthenticated()
    {
        return $this->getValue('_authenticated', false);
    }

    /**
     * サブクラスによるCloneを防ぐ
     *
     * @throws \Exception
     */
    public final function __clone()
    {
        throw new \Exception('Clone is not allowed against' . get_class($this));
    }

}
