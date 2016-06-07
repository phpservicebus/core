<?php

/*
 * INCLUDED&MODIFIED BECAUSE THE GITHUB PROJECT SEEMS TO BE DEAD.
 *
 * This file is part of Spork, an OpenSky project.
 *
 * (c) OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace acceptancesupport\PSB\Core\Fork\Util;

class Error implements \Serializable
{
    private $class;
    private $message;
    private $file;
    private $line;
    private $code;
    private $trace;

    private function __construct($class, $message, $file, $line, $code, $trace)
    {
        $this->class = $class;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
        $this->code = $code;
        $this->trace = $trace;
    }

    public static function fromException(\Exception $e)
    {
        return new static(
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getCode(),
            $e->getTraceAsString()
        );
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getTrace()
    {
        return $this->trace;
    }

    public function serialize()
    {
        return serialize(
            [
                $this->class,
                $this->message,
                $this->file,
                $this->line,
                $this->code,
                $this->trace
            ]
        );
    }

    public function unserialize($str)
    {
        list(
            $this->class,
            $this->message,
            $this->file,
            $this->line,
            $this->code,
            $this->trace
            ) = unserialize($str);
    }
}
