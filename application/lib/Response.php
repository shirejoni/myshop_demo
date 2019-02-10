<?php


namespace App\Lib;


class Response
{

    private $outPut = "";
    private $startResponseTime;
    private $endResponseTime;
    private $processTime;

    /**
     * @return mixed
     */
    public function getProcessTime()
    {
        return $this->processTime;
    }

    /**
     * @return string
     */
    public function getOutPut(): string
    {
        return $this->outPut;
    }

    /**
     * @param string $outPut
     */
    public function setOutPut(string $outPut): void
    {
        $this->outPut = $outPut;
    }

    public function OutPut() {
        echo $this->outPut;
    }
    public function startResponse() {
        $this->startResponseTime = $this->microtime_float();
    }
    public function endResponse() {
        $this->endResponseTime = $this->microtime_float();
        $this->processTime = $this->endResponseTime - $this->startResponseTime;
    }

    public function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * @return mixed
     */
    public function getStartResponseTime()
    {
        return $this->startResponseTime;
    }

    /**
     * @return mixed
     */
    public function getEndResponseTime()
    {
        return $this->endResponseTime;
    }


}