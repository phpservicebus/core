<?php
namespace PSB\Core\Util;


use PSB\Core\Exception\OutOfBoundsException;

class ContextBag
{
    /**
     * @var ContextBag
     */
    protected $parent;

    /**
     * @var array
     */
    protected $stash = [];

    /**
     * @param ContextBag|null $parent
     */
    public function __construct(ContextBag $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Searches for value of $key in this context and the parent context.
     * Returns the value if found, throws an exception it's not found.
     *
     * @param string $key
     *
     * @throws OutOfBoundsException
     * @return mixed
     */
    public function get($key)
    {
        $value = $this->tryGet($key);
        if (!$value) {
            throw new OutOfBoundsException("The given key '$key' was not present in the context.");
        }

        return $value;
    }

    /**
     * Searches for value of $key in this context and the parent context.
     * Returns the value if found, null if not found.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function tryGet($key)
    {
        if (isset($this->stash[$key])) {
            return $this->stash[$key];
        }

        if ($this->parent && $this->parent->tryGet($key) !== null) {
            return $this->parent->tryGet($key);
        }

        return null;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        $this->stash[$key] = $value;
    }

    /**
     * @param string $key
     */
    public function remove($key)
    {
        if (isset($this->stash[$key])) {
            unset($this->stash[$key]);
        }
    }
}
