<?php

namespace psf\core;

use psf\core\exceptions\HttpNotFoundException;
use psf\core\exceptions\ResourceNotFoundException;
use psf\core\exceptions\RouteNotFoundException;
use psf\core\exceptions\UnauthorizedActionException;
use psf\lib\Auth;

/**
 * アプリケーション全体の流れを制御するcoreクラス
 *
 * まず、Applicationをインスタンス化し、幾つかのスタートアップルーチンを起動する。
 * その後 {@link Application::run} を呼び出すことでリクエストを処理してレスポンスを返す。
 *
 * @package psf\core
 */
abstract class Application
{
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
     */
    public function __construct()
    {
        $this->setDebugMode();
        $this->initialize();
        $this->configure();
    }

    /**
     * デバックモードの設定を行う
     */
    protected function setDebugMode()
    {
        $debug = ConfigLoader::get('CORE','DEBUG_MODE');
        if ($debug):
            ini_set('display_errors', 1);
            error_reporting(-1);
        else:
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
        $this->router = RouteLoader::getRouter();
    }

    /**
     * データベース接続やルートの登録等のアプリケーション毎の設定を行う
     */
    protected function configure(){}

    /**
     * リクエストに対する処理を行いレスポンスを返す
     *
     * まずルーティング処理を行い、ルーティングパラメタを取得する。
     * その後、指定されたコントローラをインスタンス化して指定されたアクションを呼び出させる。
     * レスポンスはアクション内にて設定されるため、アクションの呼出し後にレスポンスを送信する。
     */
    public function run()
    {
        try
        {
            // ルーティングパラメタを取得
            $path_info = $this->request->getPathInfo();
            $routing_params = $this->router->resolve($path_info);

            // コントローラ・アクション名を取得して呼び出す
            $controller_name = $routing_params['controller'] ?? '';
            $action_name = $routing_params['action'] ?? '';
            $this->dispatchController($controller_name, $action_name, $routing_params);
        }
        catch (RouteNotFoundException $e)
        {
            $this->render404page($e);
        }
        catch (HttpNotFoundException $e)
        {
            $this->render404page($e);
        }
        catch (ResourceNotFoundException $e)
        {
            $this->render500page($e);
        }
        catch (UnauthorizedActionException $e)
        {
            $this->dispatchController(
                Auth::getLoginControllerName(),
                Auth::getLoginActionName()
            );
        }

        $this->response->send();
    }

    /**
     * 指定されたコントローラのアクションを呼び出す
     *
     * @param string $controller_name 起動するコントローラ名
     * @param string $action_name 起動するアクション名
     * @param array $params
     * @throws HttpNotFoundException
     */
    public function dispatchController(string $controller_name, string $action_name, $params = [])
    {
        // 指定されたコントローラのインスタンス化を行う
        $controller_class_name = 'app\\controllers\\' . ucfirst($controller_name) . 'Controller';
        $controller = new $controller_class_name($this);

        // 指定されたアクションを呼び出して結果をレスポンスに設定する
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
     * 500 Internal Server Error ページを返す
     *
     * @param \Exception $e
     */
    protected function render500page(\Exception $e)
    {
        $this->response->setStatusCode(500, 'Internal Server Error');
        $message = $this->isDebugMode() ? $e->getMessage() : 'Internal Server Error.';
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $this->response->setContent($message);
    }

    /**
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return ConfigLoader::get('CORE', 'DEBUG_MODE');
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