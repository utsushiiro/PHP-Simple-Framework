<?php

namespace psf\core\exceptions;

/**
 * リクエスト($url)で指定されたコンテンツが存在しないことを通知する。
 *
 * @package psf\core\exceptions
 */
class HttpNotFoundException extends \Exception
{
    public function __construct(string $message, int $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}