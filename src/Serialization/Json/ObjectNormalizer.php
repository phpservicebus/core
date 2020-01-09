<?php
namespace PSB\Core\Serialization\Json;


use PSB\Core\Exception\JsonSerializerException;
use PSB\Core\Util\Guard;

class ObjectNormalizer
{
    /**
     * @var string
     */
    private $classAnnotation;

    /**
     * Maps objects to their corresponding index. Used to deconstruct cyclic references when normalizing.
     *
     * @var \SplObjectStorage
     */
    private $objectToIndex;

    /**
     * Maps indexes to their corresponding object. Used to reconstruct cyclic references when de-normalizing.
     *
     * @var array
     */
    private $indexToObject = [];

    /**
     * @var integer
     */
    private $objectIndex = 0;


    /**
     * @param string $classAnnotation
     */
    public function __construct($classAnnotation = '@type')
    {
        Guard::againstNullAndEmpty('classAnnotation', $classAnnotation);
        $this->classAnnotation = $classAnnotation;
    }

    /**
     * @param object $object
     *
     * @return array
     */
    public function normalize($object)
    {
        if (!is_object($object)) {
            throw new JsonSerializerException("Can only serialize objects.");
        }

        $this->reset();
        return $this->normalizeObject($object);
    }

    /**
     * @param array $data
     *
     * @return object
     */
    public function denormalize(array $data)
    {
        $this->reset();
        return $this->denormalizeData($data);
    }

    private function reset()
    {
        $this->objectToIndex = new \SplObjectStorage();
        $this->indexToObject = [];
        $this->objectIndex = 0;
    }

    /**
     * Extract the data from an object
     *
     * @param object $object
     *
     * @return array
     */
    private function normalizeObject($object)
    {
        if ($this->objectToIndex->contains($object)) {
            return [$this->classAnnotation => '@' . $this->objectToIndex[$object]];
        }
        $this->objectToIndex->attach($object, $this->objectIndex++);

        $className = get_class($object);
        $normalizedObject = [$this->classAnnotation => $className];
        if ($className === 'DateTime') {
            $normalizedObject += (array) $object;
        } else {
            $normalizedObject += array_map([$this, 'normalizeValue'], $this->extractObjectProperties($object));
        }

        return $normalizedObject;
    }

    /**
     * Parse the data to be json encoded
     *
     * @param mixed $value
     *
     * @return mixed
     * @throws JsonSerializerException
     */
    private function normalizeValue($value)
    {
        if (is_resource($value)) {
            throw new JsonSerializerException("Can't serialize PHP resources.");
        }

        if ($value instanceof \Closure) {
            throw new JsonSerializerException("Can't serialize closures.");
        }

        if (is_object($value)) {
            return $this->normalizeObject($value);
        }

        if (is_array($value)) {
            return array_map([$this, 'normalizeValue'], $value);
        }

        return $value;
    }

    /**
     * Returns an array containing the object's properties to values
     *
     * @param object $object
     *
     * @return array
     */
    private function extractObjectProperties($object)
    {
        $propertyToValue = [];

        if (method_exists($object, '__sleep')) {
            $properties = $object->__sleep();
            foreach ($properties as $property) {
                $propertyToValue[$property] = $object->$property;
            }

            return $propertyToValue;
        }


        $reflectedProperties = [];
        $ref = new \ReflectionClass($object);
        foreach ($ref->getProperties() as $property) {
            $property->setAccessible(true);
            $propertyToValue[$property->getName()] = $property->getValue($object);
            $reflectedProperties[] = $property->getName();
        }

        $dynamicProperties = array_diff(array_keys(get_object_vars($object)), $reflectedProperties);

        foreach ($dynamicProperties as $property) {
            $propertyToValue[$property] = $object->$property;
        }

        return $propertyToValue;
    }

    /**
     * Parse the json decode to convert to objects again
     *
     * @param mixed $data
     *
     * @return mixed
     */
    private function denormalizeData($data)
    {
        if (is_scalar($data) || $data === null) {
            return $data;
        }

        if (isset($data[$this->classAnnotation])) {
            return $this->denormalizeObject($data);
        }

        return array_map([$this, 'denormalizeData'], $data);
    }

    /**
     * Convert the serialized array into an object
     *
     * @param array $data
     *
     * @return object
     * @throws JsonSerializerException
     */
    private function denormalizeObject(array $data)
    {
        $className = $data[$this->classAnnotation];
        unset($data[$this->classAnnotation]);

        if ($className[0] === '@') {
            $index = substr($className, 1);
            return $this->indexToObject[$index];
        }

        if (!class_exists($className)) {
            throw new JsonSerializerException("Unable to find class $className for deserialization.");
        }

        if ($className === 'DateTime') {
            $object = $this->denormalizeDateTime($className, $data);
            $this->indexToObject[$this->objectIndex++] = $object;
            return $object;
        }

        $ref = new \ReflectionClass($className);
        $object = $ref->newInstanceWithoutConstructor();
        $this->indexToObject[$this->objectIndex++] = $object;
        foreach ($data as $property => $propertyValue) {
            if ($ref->hasProperty($property)) {
                $propRef = $ref->getProperty($property);
                $propRef->setAccessible(true);
                $propRef->setValue($object, $this->denormalizeData($propertyValue));
            } else {
                $object->$property = $this->denormalizeData($propertyValue);
            }
        }

        if (method_exists($object, '__wakeup')) {
            $object->__wakeup();
        }

        return $object;
    }

    /**
     * @param string $className
     * @param array  $attributes
     *
     * @return \DateTime
     */
    private function denormalizeDateTime($className, array $attributes)
    {
        $obj = (object)$attributes;
        $serialized = preg_replace(
            '|^O:\d+:"\w+":|',
            'O:' . strlen($className) . ':"' . $className . '":',
            serialize($obj)
        );

        return unserialize($serialized);
    }
}
