<?php
namespace PSB\Core\ObjectBuilder;


use PSB\Core\Exception\ServiceNotFoundException;

interface BuilderInterface
{
    /**
     * @param string $id    The unique identifier for the parameter or object
     * @param mixed  $value The value of the parameter or a closure to define an object
     */
    public function defineSingleton($id, $value);

    /**
     * @param string   $id       The unique identifier for the parameter or object
     * @param callable $callable A service definition to be used as a factory
     */
    public function defineFactory($id, $callable);

    /**
     * @param string $id
     *
     * @return bool
     */
    public function isDefined($id);

    /**
     * @param string $id
     *
     * @return mixed
     *
     * @throws ServiceNotFoundException if the service does not exist
     */
    public function build($id);

    /**
     * @param string $id
     */
    public function dispose($id);
}
