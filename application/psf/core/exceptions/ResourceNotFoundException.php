<?php

namespace psf\core\exceptions;

/**
 * 要求されたリソースが利用できない、存在しないことを通知する
 *
 * ここでのリソースはサーバ側が内部の処理で利用しようとしたものであり、クライアントが直接要求したリソースではない。
 * クライアントが直接要求したリソースについての同様の例外は {@link HttpNotFoundException} を利用する。
 *
 * @package psf\core\exceptions
 */
abstract class ResourceNotFoundException extends \Exception
{
    /**
     * 要求されたリソース名
     *
     * @var string
     */
    protected $resource_name;


    public function __construct(string $resource_name, $message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->resource_name = $resource_name;
    }

    /**
     * @return string
     */
    public function getResourceName()
    {
        return $this->resource_name;
    }
}