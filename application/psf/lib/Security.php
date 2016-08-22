<?php

namespace psf\lib;

use psf\core\Session;

/**
 * セキュリティ対策用関数を定義するlibクラス
 *
 * TODO: CSRFトークンについてワンタイムか固定かを選べるように(現状ワンタイムのみ)
 * TODO: リファラのチェック関数作成
 * TODO: 定数類をConfig.php実装後、configで定めるように
 *
 * @package psf\lib
 */
class Security
{
    /**
     * 使用するハッシュアルゴリズム
     */
    const HASH_ALGORITHM = 'sha256';

    /**
     * 同一セッションからの最大同時フォーム利用数
     */
    const MAX_TOKEN_NUM = 10;

    /**
     * CSRFのエラーコード
     */
    const CSRF_ERROR_NUM = 100;

    /**
     * CSRFトークンを作成して返す
     *
     * @param string $form_name トークンを利用するフォーム名
     * @return string CSRFトークン
     */
    public static function generateCsrfToken(string $form_name): string
    {
        $session = Session::getInstance();
        $key = 'csrf_tokens/' . $form_name;
        $tokens = $session->getValue($key, []);

        if (count($tokens) >= self::MAX_TOKEN_NUM):
            array_shift($tokens);
        endif;

        $token = hash(self::HASH_ALGORITHM, uniqid(mt_rand(),true));
        $tokens[] = $token;

        $session->setValue($key, $tokens);

        return $token;
    }

    /**
     * 送られてきたCSRFトークンが正当なものかどうかチェックし、不正な値であった場合は RuntimeError を通知する
     *
     * @param string $form_name トークンを利用するフォーム名
     * @param string $token リクエストに設定されていたCSRFトークン
     */
    public static function checkCsrfToken(string $form_name, string $token)
    {
        $session = Session::getInstance();
        $key = 'csrf_tokens/' . $form_name;
        $tokens = $session->getValue($key, []);

        $index = array_search($token, $tokens, true);
        if ($index !== false):
            unset($tokens[$index]);
            $session->setValue($key, $tokens);
        else:
            throw new \RuntimeException('CSRF validation failed', self::CSRF_ERROR_NUM);
        endif;
    }
}