<?php

namespace core;

/**
 * Class ClassLoader
 * @package core
 *
 * 名前空間はディレクトリ階層に対応するように定める
 */
class ClassLoader
{

    /**
     * @var string フレームワークのルートディレクトリ
     * TODO:これは後に定数設定用のファイルに移す(定数として)
     */
    private $framework_root_dir;

    /**
     * @var string[] 名前空間と"これに対応するディレクトリ"までのパスの対応関係<br>
     * Foo\Bar => /some/where は 名前空間Foo\Bar が /some/where/以下に存在する、
     * つまりディレクトリ階層の /some/where/Foo/Bar に対応することを表す
     */
    private $ns2paths;


    public function __construct()
    {
        spl_autoload_register(array($this, 'loadQualifiedClass'));
        $this->ns2paths = [];
        $this->framework_root_dir = dirname(__DIR__);
    }

    /**
     * 名前空間に対応するパスを登録する
     * @param string $namespace 名前空間
     * @param string $path "名前空間に対応するディレクトリ"までのパス
     */
    public function addNameSpace(string $namespace, string $path)
    {
        $this->ns2paths[$namespace] = $path;
    }

    /**
     * $framework_root_dir以下に名前空間を登録する
     * つまり $namespace => $framework_root_dir の対応を$ns2pathsに登録する
     * @param string $namespace 名前空間
     */
    public function addFrameworkNameSpace(string $namespace)
    {
        $this->addNameSpace($namespace, $this->framework_root_dir);
    }

    /**
     * 指定されたクラスをロードする<br>
     * $classにて指定されるクラス名は修飾の有無を問わない
     * @param string $class ロードするクラス名
     * @return bool ロードの成否
     */
    private function loadQualifiedClass(string $class) : bool
    {
        // クラス名の最初の\を取り除く(いらないかも)
        $class = ltrim($class, '\\');

        // クラス名の修飾部分(名前空間指定)の有無を判定する
        $pos = strrpos($class, '\\');
        if ($pos !== false):
            $namespace = substr($class, 0, $pos);
            $class = substr($class, $pos+1);
            $res = $this->loadClass($namespace, $class);
        else:
            // 名前空間指定がないときは、$ns2pathsの$keyが''の場合に該当する
            $res = $this->loadClass('', $class);
        endif;

        return $res;
    }

    /**
     * $namespaceにある$classをロードする<br>
     * なお、$namespaceは$ns2pathsにて対応するパスが事前に登録されている必要がある<br>
     * また$classにて指定されるクラス名は非修飾名でなければならない
     * @param string $namespace $classの属する名前空間
     * @param string $class ロードするクラス名
     * @return bool ロードの成否
     */
    private function loadClass(string $namespace, string $class) : bool
    {
        foreach ($this->ns2paths as $ns => $path):
            if ($namespace === $ns):
                $load_file =
                    $path
                    . DIRECTORY_SEPARATOR
                    .str_replace('\\', DIRECTORY_SEPARATOR, $ns)
                    .DIRECTORY_SEPARATOR
                    .$class
                    .'.php';
                if (is_readable($load_file)):
                    require_once $load_file;
                    return true;
                endif;
            endif;
        endforeach;

        return false;
    }
}
