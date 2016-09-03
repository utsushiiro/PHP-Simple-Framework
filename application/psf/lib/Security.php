<?php

namespace psf\lib;

use psf\core\ConfigLoader;
use psf\core\Request;
use psf\core\Session;

/**
 * セキュリティ対策用関数を定義するlibクラス
 *
 * TODO: CSRFトークンについてワンタイムか固定かを選べるように(現状ワンタイムのみ) 実装を選べるようにStrategyで
 *
 * @package psf\lib
 */
class Security
{
    /**
     * CSRFトークンを作成して返す
     *
     * @param string $form_name トークンを利用するフォーム名
     * @return string CSRFトークン
     */
    public static function generateOneTimeCsrfToken(string $form_name): string
    {
        $session = Session::getInstance();
        $key = 'csrf_tokens/' . $form_name;
        $tokens = $session->getValue($key, []);

        $max_token_num = ConfigLoader::get('SECURITY','MAX_SAME_SESS_CON');
        if (count($tokens) >= $max_token_num):
            array_shift($tokens);
        endif;

        $hash_algorithm = ConfigLoader::get('SECURITY', 'HASH_ALGORITHM');
        $token = hash($hash_algorithm, uniqid(mt_rand(),true));
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
    public static function checkOneTimeCsrfToken(string $form_name, string $token)
    {
        $session = Session::getInstance();
        $key = 'csrf_tokens/' . $form_name;
        $tokens = $session->getValue($key, []);

        $index = array_search($token, $tokens, true);
        if ($index !== false):
            unset($tokens[$index]);
            $session->setValue($key, $tokens);
        else:
            throw new \RuntimeException('CSRF validation failed');
        endif;
    }

    /**
     * 指定されたURLがXSS脆弱性をもたないか検証する
     *
     * 動的に生成したURLがXSS脆弱性を持たないように以下の形式のURLのみを許容する。
     * <ol>
     *  <li>「http:」または「https:」で始まる絶対URL</li>
     *  <li>「./」 で始まる現在のディレクトリを起点とする相対パス参照</li>
     *  <li>「/」 で始まるドキュメントルートを起点とする絶対パス参照</li>
     * </ol>
     *
     * なお、生成したURLに関しては使用するためには別途パーセントエンコードを行う必要があるが、
     * これを行うタイミングは検証の前後のどちらでもよい。
     *
     * @param string $url 検証するURL
     * @return bool 検証結果
     */
    public static function checkSecureURL(string $url): bool
    {
        if (preg_match('#\Ahttps?:#', $url) === 1 || preg_match('#\A\.?/#', $url) === 1):
            return true;
        else:
            return false;
        endif;
    }

    /**
     * HTTPリクエストのRefererの検証を行う
     *
     * 引数 $pattern の指定がない場合は、Refererがアプリケーションのサーバ内のURLであることを正規であるとする。
     *
     * @param string $pattern 正規のRefererを表す正規表現
     * @return bool 検証結果
     */
    public static function checkReferer(string $pattern=''): bool
    {
        if ($pattern === ''):
            $request = new Request();
            $protocol = $request->isSSL() ? 'https' : 'http';
            $host = $request->getHost();
            $pattern = "#\\A${protocol}://${host}/#u";
        endif;

        $referer = $_SERVER['HTTP_REFERER'] ?? '';

        if (preg_match($pattern, $referer) === 1):
            return true;
        else:
            return false;
        endif;
    }

    /**
     * Javascriptの文字列リテラルのエスケープ処理を行う
     *
     * Javascriptの文字列リテラルを動的に生成する場合に、XSS脆弱性を持たせないようにエスケープ処理を行う。
     * 具体的には、英数字およびマイナス記号とピリオドを除く全ての文字をUnicode Escape Sequence(\uXXXX)に変換する。
     *
     * @param string $string エスケープするJavascriptの文字列リテラル
     * @return string エスケープしたJavascriptの文字列リテラル
     */
    public static function escapeJsString(string $string): string
    {
        return preg_replace_callback(
            '/[^-\.0-9a-zA-Z]+/u',
            function($matches)
            {
                $u16 = mb_convert_encoding($matches[0], 'UTF-16');
                return preg_replace('/[0-9a-f]{4}/', '\u$0', bin2hex($u16));
            },
            $string);
    }

    /**
     * $unique_key に対応する $password のハッシュ化を行う
     *
     * @param string $unique_key
     * @param string $password
     * @return string
     */
    public static function getPasswordHash(string $unique_key, string $password): string
    {
        $fixed_salt = ConfigLoader::get('SECURITY', 'FIXED_SALT_FOR_PASSWORD');
        $salt = $unique_key . pack('H*', $fixed_salt);

        $hashed_password = '';
        $stretch_num = ConfigLoader::get('SECURITY', 'STRETCH_NUM');
        $hash_algorithm = ConfigLoader::get('SECURITY', 'HASH_ALGORITHM');
        for ($i = 0; $i < $stretch_num; $i++):
            $hashed_password = hash($hash_algorithm, $hashed_password . $password . $salt);
        endfor;

        return $hashed_password;
    }
}