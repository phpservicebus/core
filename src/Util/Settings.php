<?php
namespace PSB\Core\Util;


use PSB\Core\Exception\RuntimeException;
use PSB\Core\Exception\OutOfBoundsException;

class Settings implements ReadOnlySettingsInterface
{
    /**
     * @var array
     */
    private $defaults = [];

    /**
     * @var array
     */
    private $overrides = [];

    /**
     * @var bool
     */
    private $locked = false;

    /**
     * Returns the value for the $key. Throws an exception is value is not found in overrides.
     *
     * @param string $key
     *
     * @throws OutOfBoundsException
     * @return mixed
     */
    public function get($key)
    {
        if (isset($this->overrides[$key])) {
            return $this->overrides[$key];
        }

        if (isset($this->defaults[$key])) {
            return $this->defaults[$key];
        }

        throw new OutOfBoundsException("The given key '$key' was not present in the settings.");
    }

    /**
     * Returns the value for the $key searching through overrides and defaults. Returns null if not found.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function tryGet($key)
    {
        if (isset($this->overrides[$key])) {
            return $this->overrides[$key];
        }

        if (isset($this->defaults[$key])) {
            return $this->defaults[$key];
        }

        return null;
    }

    /**
     * Returns true if there is an override or a default value for $key
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        if (isset($this->overrides[$key]) || isset($this->defaults[$key])) {
            return true;
        }

        return false;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        $this->assertWriteable();
        $this->overrides[$key] = $value;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setDefault($key, $value)
    {
        $this->assertWriteable();
        $this->defaults[$key] = $value;
    }

    private function assertWriteable()
    {
        if ($this->locked) {
            throw new RuntimeException("Settings container has been write private.");
        }
    }

    public function preventChanges()
    {
        $this->locked = true;
    }
}