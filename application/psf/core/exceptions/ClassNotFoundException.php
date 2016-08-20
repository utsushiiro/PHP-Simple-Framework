<?php

namespace psf\core\exceptions;

/**
 * クラスのオートロードに失敗したことを通知する。
 *
 * @package psf\core\exceptions
 */
class ClassNotFoundException extends \Exception
{
    private $class_name;

    public function __construct($file_name, $message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->class_name = $file_name;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->class_name;
    }
}