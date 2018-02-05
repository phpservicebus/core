<?php

namespace spec\PSB\Core\Serialization\Json\ObjectNormalizerSpec;


class AllVisibilitiesClass
{
    public $pub = 'this is public';

    protected $prot = 'protected';

    private $priv = 'dont tell anyone';

    public function getProt()
    {
        return $this->prot;
    }

    public function getPriv()
    {
        return $this->priv;
    }
}
