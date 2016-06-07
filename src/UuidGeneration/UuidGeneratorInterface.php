<?php
namespace PSB\Core\UuidGeneration;


interface UuidGeneratorInterface
{
    /**
     * @return string
     */
    public function generate();
}
