<?php
namespace PSB\Core\Util\DependencyGraph;


use PSB\Core\Exception\DependencyGraphCycleException;

class DependencyGraph
{
    /**
     * @var array
     */
    private $idToNode;

    /**
     * The actual graph maintained as an array of arrays (node id to successor ids).
     *
     * @var [][]
     */
    private $idDirectedGraph;

    /**
     * @var array
     */
    private $nodeMarks = [];

    /**
     * @var array
     */
    private $nodeTemporaryMarks = [];

    /**
     * @var array
     */
    private $sortedIdList = [];

    /**
     * @param array $idToNode        The nodes indexed by id.
     * @param array $idDirectedGraph The actual graph maintained as an array of arrays (node id to successor ids).
     */
    public function __construct(array $idToNode, array $idDirectedGraph)
    {
        $this->idToNode = $idToNode;
        $this->idDirectedGraph = $idDirectedGraph;
    }

    /**
     * It uses Tarjan's algorithm for topological sorting.
     * {@see https://en.wikipedia.org/wiki/Topological_sorting#Tarjan.27s_algorithm}
     *
     * @return array The sorted id to node list.
     */
    public function sort()
    {
        foreach ($this->idDirectedGraph as $nodeId => $successorIds) {
            if (!isset($this->nodeMarks[$nodeId])) {
                $this->visit($nodeId);
            }
        }

        $sortedIdList = array_reverse($this->sortedIdList);

        $sortedNodeList = [];
        foreach ($sortedIdList as $nodeId) {
            $sortedNodeList[] = $this->idToNode[$nodeId];
        }

        return $sortedNodeList;
    }

    /**
     * @param string $nodeId
     */
    private function visit($nodeId)
    {
        if (isset($this->nodeTemporaryMarks[$nodeId])) {
            throw new DependencyGraphCycleException(
                "Cyclic dependency detected in graph containing node with id '$nodeId'."
            );
        }

        if (isset($this->nodeMarks[$nodeId])) {
            return;
        }

        $this->nodeTemporaryMarks[$nodeId] = true;
        foreach ($this->idDirectedGraph[$nodeId] as $successorId) {
            $this->visit($successorId);
        }

        $this->nodeMarks[$nodeId] = true;
        unset($this->nodeTemporaryMarks[$nodeId]);
        $this->sortedIdList[] = $nodeId;
    }
}
