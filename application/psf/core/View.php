<?php

namespace psf\core;
use psf\core\exceptions\FileNotFoundException;

/**
 * viewファイルの実行を制御するcoreクラス
 *
 * viewファイルを実行し、レスポンスのコンテンツを作成する。
 * その際に、viewファイル内で使用される変数の展開を制御する。
 *
 * <dl>
 *  <dt>viewファイル</dt>
 *  <dd>PHPスクリプト。これを実行したものがレスポンスのコンテンツとなる。</dd>
 *  <dt>layoutファイル</dt>
 *  <dd>結果をHTMLで表示するタイプのviewファイルにおける共通部分を記述したもの。</dd>
 * </dl>
 *
 * @package psf\core
 */
class View
{

    /**
     * viewファイルの置かれているviewsディレクトリまでのパス
     *
     * @var string
     */
    protected $views_dir;

    /**
     * viewファイルにデフォルトで渡す変数
     *
     * @var array
     */
    protected $view_default_vars;

    /**
     * layoutファイルにデフォルトで渡す変数
     *
     * @var array
     */
    protected $layout_default_vars;

    /**
     * View constructor.
     * @param array $view_default_vars
     * @param string $views_dir
     */
    public function __construct($view_default_vars = [], $views_dir='../../app/views')
    {
        $this->views_dir = $views_dir;
        $this->view_default_vars = $view_default_vars;
        $this->layout_default_vars = [];
    }

    /**
     * layoutファイルにデフォルトで渡す変数を追加する
     *
     * @param string $name
     * @param mixed $value
     */
    public function setLayoutVariable(string $name, mixed $value)
    {
        $this->layout_default_vars[$name] = $value;
    }


    /**
     * 指定されたviewファイルを実行してその結果を文字列として返す
     *
     * @param array $view_vars
     * @param string $view_file_path
     * @param string $layout_file_path
     * @return string
     */
    public function buildViewFile(array $view_vars, string $view_file_path, string $layout_file_path): string
    {
        $view_file = $this->views_dir . DIRECTORY_SEPARATOR . $view_file_path . '.php';
        $view_vars = array_merge($this->view_default_vars, $view_vars);
        $content = $this->execute($view_file, $view_vars);

        $layout_file = $this->views_dir . DIRECTORY_SEPARATOR . $layout_file_path . 'php';
        $layout_vars = array_merge($this->layout_default_vars, ['_content' => $content]);
        $content = $this->execute($layout_file, $layout_vars);

        return $content;
    }

    /**
     * $varsを変数展開した後、$fileを実行(require)しその結果を返す
     *
     * アウトプットバッファリングを用いて $file を require した結果を文字列として返す。
     * $file 内では $vars の $key => $value を $key を変数名とし、$value をその値とする変数として使用できる。
     *
     * @param string $file
     * @param array $vars
     * @return string
     * @throws FileNotFoundException
     */
    private function execute(string $file, array $vars): string
    {
        if (!(is_file($file) && is_readable($file))):
            throw new FileNotFoundException($file, "File '$file' not found");
        endif;

        extract($vars);

        ob_start();
        ob_implicit_flush(0);

        require $file;

        $content = ob_get_contents();

        return $content;
    }

    /**
     * $string に含まれるHTML特殊文字をエスケープする
     *
     * viewファイル内で $this->html_escape($var) のように使用する。
     *
     * @param string $string
     * @return string
     */
    public function html_escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}