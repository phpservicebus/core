<?php
namespace acceptancesupport\PSB\Core\Scenario;

use acceptancesupport\PSB\Core\Scenario\Exception\PipeCreationException;
use acceptancesupport\PSB\Core\Scenario\Exception\PipeOpeningException;

/**
 * The FIFO pipes characteristic exploited by this synchronizer is: If the FIFO is opened for reading,
 * the process will "block" until some other process opens it for writing. This action works vice-versa as well.
 *
 * More to the point, the process waiting opens the pipe for read and the process that unlocks the other's wait
 * opens for read+write (rw makes it not block if it's the first one opening the pipe).
 */
class PipeSynchronizer
{
    /**
     * @var string
     */
    private $endpointBuilderFqcn;

    /**
     * @var string
     */
    private $pipeFilePath;

    /**
     * @param string $endpointBuilderFqcn It's used to generate a predictable FIFO pipe name
     */
    public function __construct($endpointBuilderFqcn)
    {
        $this->endpointBuilderFqcn = $endpointBuilderFqcn;
    }

    /**
     * Must be called before any other synchronization methods are.
     */
    public function createPipe()
    {
        $this->cleanup();
        $success = posix_mkfifo($this->getPipeFileName(), 0700);

        if (!$success) {
            throw new PipeCreationException(
                "Could not create named pipe '{$this->getPipeFileName()}'. Error: " . posix_strerror(
                    posix_get_last_error()
                )
            );
        }

        $handle = fopen($this->getPipeFileName(), 'r+');
        if (!$handle) {
            throw new PipeOpeningException("Could not open pipe after creation '{$this->getPipeFileName()}'.");
        }
    }

    /**
     * Hangs until some other process calls ::go
     */
    public function waitForGo()
    {
        $handle = fopen($this->getPipeFileName(), 'r');
        if (!$handle) {
            throw new PipeOpeningException("Could not open pipe '{$this->getPipeFileName()}'.");
        }

        fgets($handle);
        fclose($handle);
    }

    /**
     * Calling this unlocks all other processes hanging with ::waitForGo
     * It does not hang regardless of other hanging processes exiting or not.
     */
    public function go()
    {
        $handle = fopen($this->getPipeFileName(), 'w');
        if (!$handle) {
            throw new PipeOpeningException("Could not open pipe '{$this->getPipeFileName()}'.");
        }

        fwrite($handle, "go\n");
        fclose($handle);
    }

    public function cleanup()
    {
        @unlink($this->getPipeFileName());
    }

    private function getPipeFileName()
    {
        if (!$this->pipeFilePath) {
            $this->pipeFilePath = '/tmp/' . str_replace('\\', '-', $this->endpointBuilderFqcn) . '.pipe';
        }

        return $this->pipeFilePath;
    }
}
