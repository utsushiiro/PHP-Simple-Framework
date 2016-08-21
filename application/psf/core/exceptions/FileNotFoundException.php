<?php

namespace psf\core\exceptions;

/**
 * 指定されたパス名で示されるファイルが開けなかったことを通知する。
 *
 * @package psf\core\exceptions
 */
class FileNotFoundException extends ResourceNotFoundException
{
    public function __construct(string $file_name, string $message, int $code = 0, \Exception $previous = null)
    {
        parent::__construct($file_name, $message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->resource_name;
    }
}