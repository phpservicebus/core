<?php
namespace PSB\Core\Persistence;


use PSB\Core\Util\Settings;

abstract class PersistenceConfigurator
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
