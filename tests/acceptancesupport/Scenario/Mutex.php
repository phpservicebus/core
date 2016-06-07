<?php
namespace acceptancesupport\PSB\Core\Scenario;


use acceptancesupport\PSB\Core\Scenario\Exception\RuntimeException;

class Mutex
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var int
     */
    private $mode;

    /**
     * @var resource
     */
    private $fileHandle;

    /**
     * @var bool
     */
    private $isOpened = false;

    /**
     * @param string $filePath
     * @param int    $mode
     */
    public function __construct($filePath, $mode = 0666)
    {
        $this->filePath = $filePath;
        $this->mode = $mode;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function lock()
    {
        $this->openFile();
        return flock($this->fileHandle, LOCK_EX);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function trylock()
    {
        $this->openFile();
        return flock($this->fileHandle, LOCK_EX | LOCK_NB);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function unlock()
    {
        $this->openFile();
        return flock($this->fileHandle, LOCK_UN);
    }

    public function cleanup()
    {
        @unlink($this->filePath);
    }

    private function openFile()
    {
        if (!$this->isOpened) {
            $this->fileHandle = @fopen($this->filePath, 'w+');
            @chmod($this->filePath, $this->mode);
            if ($this->fileHandle === false) {
                throw new RuntimeException('Failed while opening file: ' . $this->filePath);
            }

            $this->isOpened;
        }
    }
}
