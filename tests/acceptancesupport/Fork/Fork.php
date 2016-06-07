<?php

/*
 * INCLUDED&MODIFIED BECAUSE THE GITHUB PROJECT SEEMS TO BE DEAD.
 *
 * This file is part of Spork, an OpenSky project.
 *
 * (c) OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace acceptancesupport\PSB\Core\Fork;


use acceptancesupport\PSB\Core\Fork\Exception\ForkException;
use acceptancesupport\PSB\Core\Fork\Exception\ProcessControlException;
use acceptancesupport\PSB\Core\Fork\Util\ExitMessage;

class Fork
{
    private $pid;

    /**
     * @var SharedMemory
     */
    private $shm;
    private $debug;
    private $name;
    private $status;
    /**
     * @var ExitMessage
     */
    private $message;
    private $messages = [];

    public function __construct($pid, SharedMemory $shm, $debug = false)
    {
        $this->pid = $pid;
        $this->shm = $shm;
        $this->debug = $debug;
        $this->name = '<anonymous>';
    }

    /**
     * Assign a name to the current fork (useful for debugging).
     *
     * @param $name
     *
     * @return Fork
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function wait($hang = true)
    {
        if ($this->isExited()) {
            return $this;
        }

        $pid = pcntl_waitpid($this->pid, $status, ($hang ? 0 : WNOHANG) | WUNTRACED);

        if ($this->pid === $pid) {
            $this->processWaitStatus($status);
        }

        return $this;
    }

    /**
     * Processes a status value retrieved while waiting for this fork to exit.
     *
     * @param $status
     */
    public function processWaitStatus($status)
    {
        if ($this->isExited()) {
            throw new \LogicException('Cannot set status on an exited fork');
        }

        $this->status = $status;

        if ($this->isExited()) {
            $this->receive();

            if ($this->debug && (!$this->isSuccessful() || $this->getError())) {
                throw new ForkException($this->name, $this->pid, $this->getError());
            }
        }
    }

    public function receive()
    {
        foreach ($this->shm->receive() as $message) {
            if ($message instanceof ExitMessage) {
                $this->message = $message;
            } else {
                $this->messages[] = $message;
            }
        }

        return $this->messages;
    }

    public function kill($signal = SIGINT)
    {
        if (false === $this->shm->signal($signal)) {
            throw new ProcessControlException('Unable to send signal');
        }

        return $this;
    }

    public function getResult()
    {
        if ($this->message) {
            return $this->message->getResult();
        }

        return null;
    }

    public function getOutput()
    {
        if ($this->message) {
            return $this->message->getOutput();
        }

        return null;
    }

    public function getError()
    {
        if ($this->message) {
            return $this->message->getError();
        }

        return null;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function isSuccessful()
    {
        return 0 === $this->getExitStatus();
    }

    public function isExited()
    {
        return null !== $this->status && pcntl_wifexited($this->status);
    }

    public function isStopped()
    {
        return null !== $this->status && pcntl_wifstopped($this->status);
    }

    public function isSignaled()
    {
        return null !== $this->status && pcntl_wifsignaled($this->status);
    }

    public function getExitStatus()
    {
        if (null !== $this->status) {
            return pcntl_wexitstatus($this->status);
        }

        return null;
    }

    public function getTermSignal()
    {
        if (null !== $this->status) {
            return pcntl_wtermsig($this->status);
        }

        return null;
    }

    public function getStopSignal()
    {
        if (null !== $this->status) {
            return pcntl_wstopsig($this->status);
        }

        return null;
    }
}
