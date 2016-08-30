<?php

namespace psf\core;
use psf\core\exceptions\HttpNotFoundException;

/**
 * ユーザ定義の各種コントローラの基底となるcoreクラス
 *
 * {@link Application::dispatchController} 内でルーティングパラメタに指定されたコントローラがインスタンス化され、
 * これを介して {@link dispatchAction} が呼ばれ、指定されたアクションが実行される。
 *
 * @package psf\core
 * @property string action_name アクション実行中のみ、そのアクション名が設定される
 */
abstract class Controller
{

    /**
     * コントローラ名
     *
     * XXXControllerの'XXX'部分をすべて小文字にしたもの
     * つまり、ルーティングパラメタの controller の値
     *
     * @var string
     */
    protected $controller_name;

    /**
     * @var Application
     */
    protected $application;

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
     * Controller constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->controller_name = strtolower(substr(get_class($this), 0, -10));
        $this->application = $application;
        $this->request = $application->getRequest();
        $this->response = $application->getResponse();
        $this->session = $application->getSession();
        $this->db_manager = $application->getDbManager();
    }

    /**
     * アクションを呼び出してその結果を返す
     *
     * @param string $action_name 呼び出すアクション名
     * @param array $params アクションに渡すパラメータ
     * @return string アクションの実行結果
     */
    public function dispatchAction(string $action_name, array $params = []): string
    {
        $this->action_name = $action_name;
        $action_method_name = $action_name . 'Action';

        $content = '';
        if (method_exists($this, $action_method_name)):
            $content = $this->$action_method_name($params);
        else:
            $this->forward404();
        endif;

        unset($this->action_name);
        return $content;
    }

    /**
     * 指定されたviewファイルを実行してその結果を文字列として返す
     *
     * {@link View::buildViewFile} のラッパー
     *
     * @param array $view_vars
     * @param string $view_file_name
     * @param string $layout_file_path
     * @return string
     */
    protected function render(array $view_vars = [], string $view_file_name = '',
                              string $layout_file_path='layout'): string
    {
        $view_default_vars = [
            'request'  => $this->request,
            'base_url' => $this->request->getBaseUri(),
            'session'  => $this->session
        ];

        $view = new View($view_default_vars);
        
        if ($view_file_name === ''):
            $view_file_name = $this->action_name;
        endif;

        $view_file_path = $this->controller_name . '/' . $view_file_name;

        $content = $view->buildViewFile($view_vars, $view_file_path, $layout_file_path);

        return $content;
    }

    /**
     * HttpNotFoundException を通知し、404エラー画面へ遷移させる
     *
     * このメソッドはアクション内で呼ばれた場合、404エラー画面への遷移を保証する。
     * この場合は、{@link dispatchAction}, {@link Application::dispatchController},
     * {@link Application::run} の順に遡り、最後の {@link Application::run} において、
     * 例外が補足され {@link Application::render404page} により404エラー画面へ遷移する。
     *
     * それ以外の場合は、HttpNotFoundException が補足されることは保証されない。
     * この場合、遷移元のアクション部分の表示は'_undefined_'となる
     *
     * @throws HttpNotFoundException
     */
    protected function forward404()
    {
        $url = $this->controller_name . '/' . ($this->action_name ?? '_undefined_');
        throw new HttpNotFoundException('Forwarded 404 page from ' . $url);
    }

    /**
     * $url にリダイレクトするようにレスポンスを設定する
     *
     * このアプリケーション内へのリダイレクトを行う場合、パスインフォ部のみを指定すればよい。
     *
     * @param string $url
     */
    protected function redirect(string $url)
    {
        if (!preg_match('#https?://#', $url)):
            $protocol = $this->request->isSSL() ? 'https://' : 'http://';
            $host = $this->request->getHost();
            $base_url = $this->request->getBaseUri();
            $url = $protocol . $host . $base_url . $url;
        endif;

        $this->response->setStatusCode('302', 'Found');
        $this->response->setHttpHeader('Location', $url);
    }
}