<?php

namespace psf\core;
use psf\core\exceptions\HttpNotFoundException;
use psf\core\exceptions\UnauthorizedActionException;
use psf\lib\Auth;

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
     * @var Auth
     */
    protected $auth;

    /**
     * Controller constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $qualified_class_name = strtolower(substr(get_class($this), 0, -10));
        $pieces = explode('\\', $qualified_class_name);
        $this->controller_name = end($pieces);

        $this->application = $application;
        $this->request = $application->getRequest();
        $this->response = $application->getResponse();
        $this->session = $application->getSession();
        $this->db_manager = $application->getDbManager();
        $this->auth = new Auth($this->session);

        $this->configure();
    }

    /**
     * コントローラ初期化時に行う処理を記述する
     *
     * ユーザがControllerクラスをextendsして作成するコントローラにおいて、
     * コントローラの初期化の際に必要な処理(認証が必要なアクションの登録等)があった場合、
     * このメソッドをオーバーライドしてその処理を記述する。
     *
     * このメソッドは {@link Controller::__construct} の最後に呼ばれる。
     */
    public function configure(){}

    /**
     * アクションを呼び出してその結果を返す
     *
     * @param string $action_name 呼び出すアクション名
     * @param array $params アクションに渡すパラメータ
     * @return string アクションの実行結果
     * @throws UnauthorizedActionException
     */
    public function dispatchAction(string $action_name, array $params = []): string
    {
        $this->action_name = $action_name;
        $action_method_name = $action_name . 'Action';

        if (!method_exists($this, $action_method_name)):
            $this->forward404();
        endif;

        if ($this->auth->needsAuthentication($action_name) && !$this->auth->isAuthenticated()):
            throw new UnauthorizedActionException();
        endif;

        $content = $this->$action_method_name($params);

        unset($this->action_name);
        return $content;
    }

    /**
     * 指定されたviewファイルを実行してその結果を文字列として返す
     *
     * {@link View::render} のラッパー
     *
     * @param array $view_vars
     * @param string $view_filename
     * @param string $layout_filename
     * @return string
     */
    protected function render(array $view_vars = [], string $view_filename = '',
                              string $layout_filename = ''): string
    {
        $default_vars = [
            'request'  => $this->request,
            'base_url' => $this->request->getBaseUri(),
            'session'  => $this->session
        ];

        if ($view_filename === ''):
            $view_filename = $this->action_name;
        endif;
        $view_path = $this->controller_name . DIRECTORY_SEPARATOR . $view_filename;

        $view = new View($view_path, $default_vars);

        if ($layout_filename !== ''):
            $view->setLayoutFile($layout_filename);
        elseif ($layout_filename === '' && ConfigLoader::get('LAYOUT', 'USE_DEFAULT_LAYOUT')):
            $layout_filename = ConfigLoader::get('LAYOUT', 'DEFAULT_LAYOUT_FILENAME');
            $view->setLayoutFile($layout_filename);
        endif;

        $content = $view->render($view_vars);

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
     * $url にリダイレクトする
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
        $this->response->send();
    }
}