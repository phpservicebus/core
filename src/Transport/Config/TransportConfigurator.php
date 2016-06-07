<?php
namespace PSB\Core\Transport\Config;


use PSB\Core\Util\Settings;

abstract class TransportConfigurator
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
