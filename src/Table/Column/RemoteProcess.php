<?php

namespace FrenchFrogs\Table\Column;

/**
 * Trait for column with remote process.
 *
 * Class RemoteProcess
 */
trait RemoteProcess
{
    /**
     * frunction to process the column.
     *
     * @var callable
     */
    protected $remoteProcess;

    /**
     * Return TRUE if remote process is set.
     *
     * @return bool
     */
    public function hasRemoteProcess()
    {
        return isset($this->remoteProcess) && is_callable($this->remoteProcess);
    }

    /**
     * Set $remoteProcess.
     *
     * @param $function
     *
     * @return $this
     */
    public function setRemoteProcess($function)
    {
        if (!is_callable($function)) {
            throw new \LogicException('"'.$function.'" is not callable');
        }

        $this->remoteProcess = $function;

        return $this;
    }

    /**
     * Getter for $remoteProcess.
     *
     * @return callable
     */
    public function getRemoteProcess()
    {
        return $this->remoteProcess;
    }

    /**
     * Execute $remoteProcess with params.
     *
     * @param array ...$params
     *
     * @return mixed
     */
    public function remoteProcess(...$params)
    {
        return call_user_func_array($this->remoteProcess, $params);
    }
}
