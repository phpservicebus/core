<?php
namespace PSB\Core\Util\DependencyGraph;


interface GraphBuilderInterface
{
    /**
     * @return DependencyGraph
     */
    public function build();
}
