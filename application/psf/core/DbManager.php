<?php

namespace psf\core;

/**
 * データベースとのコネクション群を管理するcoreクラス
 *
 * @package psf\core
 */
class DbManager
{
    /**
     * データベースのコネクションプール
     *
     * コネクション名 => コネクション の連想配列
     *
     * @var \PDO[]
     */
    private $connections;

    /**
     * レポジトリ名とコネクション名の対応関係
     *
     * レポジトリ名 => コネクション名 の連想配列
     *
     * @var
     */
    private $repo2cons;


    /**
     * 使用されている{@link Repository}集合
     *
     * レポジトリ名 => レポジトリオブジェクト の連想配列
     *
     * @var DbRepository[]
     */
    private $repositories;


    /**
     * DbManager constructor.
     */
    public function __construct()
    {
        $this->connections = [];
        $this->repo2cons = [];
        $this->repositories = [];
    }

    /**
     * この DbManager の管理するDBコネクション(PDOオブジェクト)の破棄を行う
     *
     * コネクションプール $connections に加え、 $repositories に含まれる各レポジトリオブジェクトも
     * 内部にてコネクションへの参照を保持しているため、まず $repositories に含まれるレポジトリオブジェクトを
     * 破棄した後に、$connections に含まれるコネクションを破棄する。
     */
    function __destruct()
    {
        foreach ($this->repositories as $repository) :
            unset($repository);
        endforeach;

        foreach ($this->connections as $connection) :
            unset($connection);
        endforeach;
    }

    /**
     * DBコネクションを確立する
     *
     * PDOによるDBコネクション$nameを、$paramsを元に確立する
     *
     * @param string $con_name コネクション名
     * @param array $params PDOコンストラクタに渡すDB設定のパラメータ
     */
    public function connect(string $con_name, array $params)
    {
        if (!isset($params['dsn'])):
            throw new \InvalidArgumentException('The \'dsn\' parameter in $params is empty.');
        endif;

        if (!isset($params['user'])):
            throw new \InvalidArgumentException('The \'user\' parameter in $params is empty.');
        endif;

        if (!isset($params['password'])):
            throw new \InvalidArgumentException('The \'password\' parameter in $params is empty.');
        endif;

        $connection = new \PDO(
            $params['dsn'],
            $params['user'],
            $params['password'],
            $params['options']
        );

        // エラー処理を例外で行う
        $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // 静的プレースホルダを利用
        $connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

        $this->connections[$con_name] = $connection;
    }

    /**
     * コネクション名から対応するコネクション(PDOオブジェクト)を習得する
     *
     * TODO: 存在しない名前を指定されたときに例外を吐くようにする
     *
     * @param string $con_name
     * @return \PDO
     */
    public function getConnection(string $con_name) : \PDO
    {
        if (is_null($con_name)):
            return current($this->connections);
        endif;
        return null;
    }

    /**
     * レポジトリ名とコネクション名を対応付ける
     *
     * @param string $repo_name レポジトリ名
     * @param string $con_name コネクション名
     */
    public function setRepo2con(string $repo_name, string $con_name)
    {
        $this->repo2cons[$repo_name] = $con_name;
    }

    /**
     * レポジトリ名から対応するコネクション(PDOオブジェクト)を習得する
     *
     * @param string $repo_name
     * @return null|\PDO
     */
    public function getConnectionForRepo(string $repo_name)
    {
        $connection = null;
        if (isset($this->repo2cons[$repo_name])):
            $con_name = $this->getConnection($repo_name);
            $connection = $this->getConnection($con_name);
        endif;
        return $connection;
    }

    /**
     * レポジトリ名に対応する{@link DbRepository}オブジェクトを返す
     *
     * レポジトリ名に対応する DbRepository オブジェクトを、$repositories を参照して返す。
     * 対応がなかった場合は、新たに DbRepository オブジェクトを作成し、$repositories に登録してこれを返す。
     * この際に作成される DbRepository オブジェクトは、DbRepository のサブクラスであり、
     * このサブクラスの名前は「'レポジトリ名' . 'Repository'」でなければならない。
     *
     * @param $repo_name string レポジトリ名
     * @return DbRepository 対応するレポジトリオブジェクト
     */
    public function getRepo(string $repo_name)
    {
        if (!isset($this->repositories[$repo_name])):
            $repo_class_name = 'app\\models\\' . $repo_name . 'Repository';
            $connection = $this->getConnectionForRepo($repo_name);

            $repository = new $repo_class_name($connection);

            $this->repositories[$repo_name] = $repository;
        endif;

        return $this->repositories[$repo_name];
    }
}
