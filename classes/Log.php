<?php
/**
 * Universal logging tool
 */
class Log
{
    /** @var string $file */
    private $file = null;

    /**
     * Log constructor.
     * @param string $fileName
     */
    public function __construct(string $fileName)
    {
        $this->file = $fileName;
    }
}