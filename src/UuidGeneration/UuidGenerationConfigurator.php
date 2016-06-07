<?php
namespace PSB\Core\UuidGeneration;


use PSB\Core\Util\Settings;

abstract class UuidGenerationConfigurator
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
