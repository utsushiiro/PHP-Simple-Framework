<?php

namespace psf\core\exceptions;

/**
 * クラスのオートロードに失敗したことを通知する。
 *
 * @package psf\core\exceptions
 */
class ClassNotFoundException extends ResourceNotFoundException
{
    public function __construct($class_name, $message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($class_name ,$message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->resource_name;
    }
}