<?php

namespace psf\core;

use psf\core\exceptions\ClassNotFoundException;
use psf\core\exceptions\RouteNotFoundException;

/**
 * アプリケーション全体の流れを制御するcoreクラス
 *
 * @package psf\core
 */
abstract class Application
{
    /**
     * デバックモードのOn/Off
     *
     * @var bool
     */
    protected $debug;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var DbManager
     */
    protected $db_manager;

    /**
     * @var Router
     */
    protected $router;

    /**
     * Application constructor.
     * @param bool $debug
     */
    public function __construct(bool $debug)
    {
        $this->setDebugMode($debug);
        $this->initialize();
        $this->configure();
    }

    /**
     * デバックモードの設定を行う
     *
     * @param bool $debug
     */
    protected function setDebugMode(bool $debug)
    {
        if ($debug):
            $this->debug = true;
            ini_set('display_errors', 1);
            error_reporting(-1);
        else:
            $this->debug = false;
            ini_set('display_errors', 0);
        endif;
    }

    /**
     * 基本的なcoreクラスをインスタンス化する
     *
     * 具体的には以下のクラスをインスタンス化し、メンバ変数に保持する。
     * <ol>
     *  <li>リクエスト情報を制御する {@link Request} クラス</li>
     *  <li>レスポンス情報を制御する{@link Response} クラス</li>
     *  <li>セッションを管理する {@link Session} クラス</li>
     *  <li>データベースとのコネクション群を管理する {@link DbManager} クラス</li>
     *  <li>ルーティングを制御する {@link Router} クラス</li>
     * </ol>
     */
    protected function initialize()
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->session = Session::getInstance();
        $this->db_manager = new DbManager();
        $this->router = new Router();
    }

    /**
     * データベース接続やルートの登録等のアプリケーション毎の設定を行う
     */
    protected function configure(){}

    /**
     * リクエストに対する処理を行いレスポンスを返す
     */
    public function run()
    {
        $routing_params = null;
        try{
            $path_info = $this->request->getPathInfo();
            $routing_params = $this->router->resolve($path_info);
        }catch (RouteNotFoundException $e){
            $this->render404page($e);
        }

        $controller_name = $routing_params['controller'] ?? '';
        $action_name = $routing_params['action'] ?? '';

        try {
            $this->dispatchController($controller_name, $action_name, $routing_params);
        } catch (ClassNotFoundException $e) {
            $this->render404page($e);
        }

        $this->response->send();
    }

    /**
     * 指定されたコントローラのアクションを呼び出す
     *
     * @param string $controller_name
     * @param string $action_name
     * @param array $params
     */
    public function dispatchController(string $controller_name, string $action_name, $params = [])
    {
        $controller_class = ucfirst($controller_name) . 'Controller';
        $controller = new $controller_class($this);
        $content = $controller->dispatchAction($action_name, $params);
        $this->response->setContent($content);
    }

    /**
     * 404 Not Found ページを返す
     *
     * TODO: これを処理するコントローラやビューファイルを作成する
     * @param \Exception $e
     */
    protected function render404page(\Exception $e)
    {
        $this->response->setStatusCode(404, 'Not Found');
        $message = $this->isDebugMode() ? $e->getMessage() : 'Page not found.';
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $this->response->setContent($message);
    }

    /**
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->debug;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * @return DbManager
     */
    public function getDbManager(): DbManager
    {
        return $this->db_manager;
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
}