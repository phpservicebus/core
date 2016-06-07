<?php
namespace PSB\Core\Util;


use PSB\Core\Exception\OutOfBoundsException;

interface ReadOnlySettingsInterface
{
    /**
     * Returns the value for the $key. Throws an exception is value is not found in overrides.
     *
     * @param string $key
     *
     * @throws OutOfBoundsException
     * @return mixed
     */
    public function get($key);

    /**
     * Returns the value for the $key searching through overrides and defaults. Returns null if not found.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function tryGet($key);

    /**
     * Returns true if there is an override or a default value for $key
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key);
}