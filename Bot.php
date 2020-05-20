<?php

namespace App;

final class Bot extends MarketData {

    public $positions, $size;


    public function __construct() {

        parent::__construct();
    }
    



    public function CheckPositions(){
/*
        $positions = $this->getOpenPositions();

        if(!$positions)
        {
            echo "No open positions" . PHP_EOL;
        }
        else {
            foreach($positions as $position)
            {
                $qty = $position['currentQty'];
                $entry = $position['avgEntryPrice'];
                $symbol = $position['symbol'];
                echo $qty > 0
                    ? "LONG $qty contracts on $symbol\nAverage entry: $$entry\n"
                    : "SHORT $qty contracts on $symbol\nAverage entry: $$entry\n";
            }
        }*/
        return $this->getOpenPositions();

    }





    public function Buy($size){

        echo "Buying $size contracts @ market($this->close)" . PHP_EOL;
        return $buy_order = $this->createOrder("Market", "Buy", NULL, $size);
    }


    public function Sell($size){
        
        echo "Selling $size contracts @ market($this->close)" . PHP_EOL;
        return $sell_order = $this->createOrder("Market", "Sell", NULL, $size);
    }


    public function CloseLong($size){

        $tp = $this->close * (1 + $this->config->trading->take_profit / 100);
        echo "Placing TP at $tp" . PHP_EOL;
        return $close = $this->createOrder("Limit", "Sell", round($tp), $size, "ReduceOnly");
    }


    public function CloseShort($size){

        $tp = $this->close * (1 - $this->config->trading->take_profit / 100);
        echo "Placing TP at $tp" . PHP_EOL;
        return $close = $this->createOrder("Limit", "Buy", round($tp), $size, "ReduceOnly");
    }


    public function showConfig() {
        
        return $this->config;
    }

}