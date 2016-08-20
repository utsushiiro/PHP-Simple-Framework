<?php

namespace psf\core;

/**
 * データベースとの特定の１つのコネクションを管理するcoreクラス
 *
 * @package psf\core
 */
abstract class DbRepository
{
    /**
     * レポジトリに対応するコネクション
     *
     * @var \PDO
     */
    private $connection;

    /**
     * DbRepository constructor.
     * @param \PDO $connection
     */
    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * 与えられたSQL文($sql)を実行する
     *
     * SQL文の構築はプリペアードステートメント({@link \PDO::prepare})を用いて行われる。
     * $paramsではプリペアードステートメントのプレースホルダ部に入れる値を指定する。
     *
     * @param string $sql
     * @param array $params
     * @return \PDOStatement
     */
    public function executeSQL(string $sql, array $params = [])
    {

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return mixed
     */
    public function fetch(string $sql, array $params = [])
    {
        $stmt = $this->executeSQL($sql, $params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function fetchAll(string $sql, array $params = [])
    {
        $stmt = $this->executeSQL($sql, $params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}