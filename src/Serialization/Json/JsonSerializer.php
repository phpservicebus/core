<?php
namespace PSB\Core\Serialization\Json;


class JsonSerializer
{
    /**
     * @var ObjectNormalizer
     */
    private $normalizer;

    /**
     * @var JsonEncoder
     */
    private $encoder;

    /**
     * @param ObjectNormalizer $normalizer
     * @param JsonEncoder      $encoder
     */
    public function __construct(ObjectNormalizer $normalizer, JsonEncoder $encoder)
    {
        $this->normalizer = $normalizer;
        $this->encoder = $encoder;
    }

    /**
     * @param object $object
     *
     * @return string
     */
    public function serialize($object)
    {
        return $this->encoder->encode($this->normalizer->normalize($object));
    }

    /**
     * @param string $json
     *
     * @return object
     */
    public function unserialize($json)
    {
        return $this->normalizer->denormalize($this->encoder->decode($json));
    }
}
