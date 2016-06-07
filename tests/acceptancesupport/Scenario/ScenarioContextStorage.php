<?php
namespace acceptancesupport\PSB\Core\Scenario;


class ScenarioContextStorage
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var Mutex
     */
    private $mutex;

    /**
     * @param string $filePath
     * @param Mutex  $mutex
     */
    public function __construct($filePath, Mutex $mutex)
    {
        $this->filePath = $filePath;
        $this->mutex = $mutex;
    }

    /**
     * @param ScenarioContextProxy $contextProxy
     */
    public function write(ScenarioContextProxy $contextProxy)
    {
        file_put_contents($this->filePath, serialize($contextProxy->getContext()));
    }

    /**
     * @return ScenarioContext|null
     */
    public function read()
    {
        if (file_exists($this->filePath)) {
            $serializedContext = file_get_contents($this->filePath);
            if ($serializedContext) {
                return unserialize($serializedContext);
            }
        }

        return null;
    }

    public function cleanup()
    {
        @unlink($this->filePath);
        $this->mutex->cleanup();
    }

    /**
     * @return bool
     */
    public function lock()
    {
        return $this->mutex->lock();
    }

    /**
     * @return bool
     */
    public function unlock()
    {
        return $this->mutex->unlock();
    }
}
