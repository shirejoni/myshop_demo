<?php


namespace App\Lib;


class Response
{

    private $outPut = "";

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

}