<?php
namespace PSB\Core\Serialization;


use PSB\Core\Util\Settings;

abstract class SerializationConfigurator
{
    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }
}
