<?php

namespace psf\lib;
use psf\core\Session;

/**
 * 認証(Authenticate)処理を行うためのlibクラス
 *
 * @package psf\lib
 */
class Auth
{
    /**
     * @var Session
     */
    private $session;

    /**
     * 認証が必要なアクション一覧
     *
     * @var array
     */
    private $auth_actions;

    /**
     * すべてのアクションが認証を必要とするかどうかのフラグ
     *
     * @var bool
     */
    private $auth_all_actions;

    /**
     * ログイン処理を行うコントローラ
     *
     * @var string
     */
    private $login_controller_name;

    /**
     * ログイン処理を行うアクション
     *
     * @var string
     */
    private $login_action_name;

    /**
     * Auth constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->auth_actions = [];
    }

    /**
     * 認証状態を$statusに変更後、新たなセッションIDを発行し乗り換える
     *
     * @param bool $status 認証状態
     */
    public function setAuthenticated(bool $status)
    {
        $this->session->setValue('_authenticated', $status);
        session_regenerate_id(true);
    }

    /**
     * アクションに認証が必要かどうかを判断する
     *
     * @param string $action_name
     * @return bool 必要な場合は true そうでなければ false
     */
    public function needsAuthentication(string $action_name)
    {
        if ($this->auth_all_actions || in_array($action_name, $this->auth_actions, true)):
            return true;
        else:
            return false;
        endif;
    }

    /**
     * 現在の認証状態を返す
     *
     * @return string
     */
    public function isAuthenticated()
    {
        return $this->session->getValue('_authenticated', false);
    }

    /**
     * 認証が必要なアクションを登録する
     *
     * @param string $action_name アクション名
     */
    public function addAuthAction(string $action_name)
    {
        $this->auth_actions[] = $action_name;
    }

    /**
     * すべてのアクションに認証を必要とするかどうかのフラグを設定する
     *
     * 引数を省略もしくは true を指定した場合、このオブジェクトの{@link needsAuthentication}の返り値(結果)は必ずtrueとなる。
     * これはすべてのアクションに認証が必要な場合に {@link addAuthAction} による登録処理の代替となる。
     *
     * @param bool $bool
     */
    public function setAuthAllActions(bool $bool=true)
    {
        $this->auth_all_actions = $bool;
    }

    /**
     * ログイン処理を行うコントローラとアクションを設定する
     *
     * @param string $controller_name
     * @param string $action_name
     */
    public function setLoginAction(string $controller_name, string $action_name)
    {
        $this->login_controller_name = $controller_name;
        $this->login_action_name = $action_name;
    }
}