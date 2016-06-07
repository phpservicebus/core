<?php
namespace PSB\Core\ObjectBuilder;


use Interop\Container\ContainerInterface as InteropContainerInterface;
use PSB\Core\Exception\ServiceNotFoundException;

class Builder implements BuilderInterface
{
    /**
     * @var Container
     */
    private $internalContainer;

    /**
     * @var InteropContainerInterface
     */
    private $externalContainer;

    /**
     * @param Container                      $internalContainer
     * @param InteropContainerInterface|null $externalContainer
     */
    public function __construct(
        Container $internalContainer,
        InteropContainerInterface $externalContainer = null
    ) {
        $this->internalContainer = $internalContainer;
        $this->externalContainer = $externalContainer;
    }

    /**
     * @param string $id    The unique identifier for the parameter or object
     * @param mixed  $value The value of the parameter or a closure to define an object
     */
    public function defineSingleton($id, $value)
    {
        $this->internalContainer->offsetSet($id, $value);
    }

    /**
     * @param string   $id       The unique identifier for the parameter or object
     * @param callable $callable A service definition to be used as a factory
     */
    public function defineFactory($id, $callable)
    {
        $this->internalContainer->offsetSet($id, $this->internalContainer->factory($callable));
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function isDefined($id)
    {
        return $this->internalContainer->offsetExists($id) ||
        $this->externalContainer && $this->externalContainer->has($id);
    }

    /**
     * @param string $id
     *
     * @return mixed
     *
     * @throws ServiceNotFoundException if the service does not exist
     */
    public function build($id)
    {
        if ($this->internalContainer->offsetExists($id)) {
            return $this->internalContainer->offsetGet($id);
        }

        if ($this->externalContainer && $this->externalContainer->has($id)) {
            return $this->externalContainer->get($id);
        }

        throw new ServiceNotFoundException("Service '$id' not found in any of the containers.");
    }

    /**
     * @param string $id
     */
    public function dispose($id)
    {
        $this->internalContainer->offsetUnset($id);
    }
}
