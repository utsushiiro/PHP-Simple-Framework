<?php

namespace psf\core;

/**
 * レスポンス情報を制御するcoreクラス<br>
 *
 * @package psf\core
 */
class Response
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var int
     */
    private $status_code = 200;

    /**
     * @var string
     */
    private $status_text = 'OK';

    /**
     * HTTPヘッダを格納する連想配列
     * @var string[]
     */
    private $http_header = [];

    /**
     * HTTPヘッダを設定し、レスポンスを送信する
     *
     * ステータスラインは$status_code, $status_textを元に、ヘッダは$http_headerを元に、
     * レスポンスボディは$contentsを元に作成する。
     */
    public function send()
    {
        header('HTTP/1.1 ' . $this->status_code . ' ' . $this->status_text);

        foreach ($this->http_header as $name => $value):
            header($name . ': ' . $value);
        endforeach;

        echo $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content)
    {
        $this->content = $content;
    }

    /**
     * @param int $status_code
     * @param string $status_text
     */
    public function setStatusCode(int $status_code, string $status_text = '')
    {
        $this->status_code = $status_code;
        $this->status_text = $status_text;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setHttpHeader(string $name, string $value)
    {
        $this->http_header[$name] = $value;
    }
}