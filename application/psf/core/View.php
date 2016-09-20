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
     * viewファイル名
     *
     * @var string
     */
    protected $view_file;

    /**
     * viewファイルにデフォルトで渡す変数
     *
     * @var array
     */
    protected $default_vars;

    /**
     * layoutファイルを利用するかどうか
     *
     * @var bool
     */
    protected $has_layout = false;

    /**
     * layoutファイル名
     *
     * @var string
     */
    protected $layout_file;

    /**
     * layoutファイルに渡す変数
     *
     * @var array
     */
    protected $layout_vars;


    /**
     * View constructor.
     *
     * $pathには実行するviewファイルへのVIEWS_ROOTからのパスを指定する。
     * このviewファイルの実行は {@link render} の呼び出しによって行われる。
     *
     * $default_varsに指定する連想配列は、このViewオブジェクトを通じて実行されるviewファイル
     * ($pathに指定したviewファイル以外にlayoutファイルや {@link includeView} で実行するviewファイル)
     * において変数として展開される。つまり、インデックス名を変数名として対応する値に変数としてアクセスできる。
     * 例えば、["xxx" => 10] を指定した場合、各viewファイル内では $xxx で 10 にアクセスできる。
     *
     * @param string $path VIEWS_ROOTからのviewファイルまでのパス (ファイルの拡張子は含めない)
     * @param array $default_vars viewファイルにデフォルトで渡す変数
     */
    public function __construct(string $path, array $default_vars = [])
    {
        $this->views_dir = ConfigLoader::get('PATH', 'VIEWS_ROOT');
        $this->view_file = $this->views_dir . DIRECTORY_SEPARATOR . $path . '.php';
        $this->default_vars = $default_vars;
    }

    /**
     * layoutファイルを指定する
     *
     * @param string $path LAYOUTS_DIRからのlayoutファイルまでのパス (ファイルの拡張子は含めない)
     */
    public function setLayoutFile(string $path)
    {
        $layouts_dir = $this->views_dir . DIRECTORY_SEPARATOR . ConfigLoader::get('LAYOUT', 'LAYOUTS_DIR');
        $this->layout_file = $layouts_dir . DIRECTORY_SEPARATOR . $path . '.php';

        $this->has_layout = true;
        $this->layout_vars = [];
    }

    /**
     * layoutファイルに渡す変数を追加する
     *
     * @param string $name 変数名
     * @param mixed $value 変数の値
     */
    public function addLayoutVar(string $name, $value)
    {
        if (!$this->has_layout):
            throw new \RuntimeException("The layout file has not been set.");
        endif;

        $this->layout_vars[$name] = $value;
    }

    /**
     * viewファイルを実行後、指定があればlayoutファイルを実行しその結果を返す
     *
     * @param array $view_vars viewファイルに渡す変数
     * @return string
     */
    function render(array $view_vars = []): string
    {
        $view_vars = array_merge($this->default_vars, $view_vars);
        $content = $this->execute($this->view_file, $view_vars);

        if ($this->has_layout):
            $layout_vars = array_merge($this->layout_vars, ['_content' => $content]);
            $layout_vars = array_merge($layout_vars, $this->default_vars);
            $content = $this->execute($this->layout_file, $layout_vars);
        endif;

        return $content;
    }

    /**
     * 指定されたviewファイルを実行し、結果を文字列として返す
     *
     * viewファイル内で他のviewファイルを実行するために使用する
     *
     * @param string $path VIEWS_ROOTからのviewファイルまでのパス
     * @param array $view_vars viewファイルに渡す変数
     * @return string
     */
    public function includeView(string $path, array $view_vars = [])
    {
        $view_file = $this->views_dir . DIRECTORY_SEPARATOR . $path . '.php';
        $view_vars = array_merge($this->default_vars, $view_vars);
        $content = $this->execute($view_file, $view_vars);

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
    protected function execute(string $file, array $vars): string
    {
        if (!(is_file($file) && is_readable($file))):
            throw new FileNotFoundException($file, "File '$file' not found");
        endif;

        extract($vars);

        ob_start();
        ob_implicit_flush(0);

        require $file;

        $content = ob_get_clean();

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