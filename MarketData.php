<?php

namespace App;

class MarketData extends BitMex {


    const CONFIG = __DIR__ . "/config.json";
    const INDICATOR_VALUES = __DIR__ . "/indicators.json";

    public $config, $last_indicator_values;
    public $open, $high, $low, $close, $previous_close;
    private $candle_data;
    
    


    public function __construct() {
        
        $this->config = json_decode(file_get_contents(self::CONFIG));
        $id = $this->config->api_keys->id;
        $secret = $this->config->api_keys->secret;
        parent::__construct($id, $secret);

        $this->candle_data = $this->GetMarketData();
        $this->ImportLastIndicatorValues();


    }


    public function GetSignal(): string
    {
        $last_atrx = $this->last_indicator_values->last_atr_stop;

        $long_signal = $this->previous_close < $last_atrx && $this->close > $last_atrx;
        $short_signal = $this->previous_close > $last_atrx && $this->close < $last_atrx;

        #   return 1 for long signal, -1 for short signal, 0 for no signal
        if($long_signal){
            return "BUY";
        }
        elseif($short_signal){
            return "SELL";
        }
        else{
            return "NO SIGNAL";
        }
    }



    private function GetMarketData(): array {

        $candlesBufferArray = $this->getCandles("5m", 15);
        //$candlesBufferArray = $this->makeCandles("5m", 63);

        #   create arrays for highs, lows and closes for indicator calc
        foreach($candlesBufferArray as $candle){
            $highBufferArray[] = $candle['high'];
            $lowBufferArray[] = $candle['low'];
            $closeBufferArray[] = $candle['close'];
        }

        $this->open = $candlesBufferArray[0]['open'];
        $this->high = $candlesBufferArray[0]['high'];
        $this->low = $candlesBufferArray[0]['low'];
        $this->close = $candlesBufferArray[0]['close'];
        $this->previous_close = $candlesBufferArray[1]['close'];


        
        return ["candles" => $candlesBufferArray,
                "highs" => $highBufferArray,
                "lows" => $lowBufferArray,
                "closes" => $closeBufferArray];
    }





    private function makeCandles($timeframe, $quantity): array {
        
        # funcion para armar velas en temporalidades
        # distintas a las que ofrece BitMEX
        $building_candles = $this->getCandles($timeframe, $quantity);    # 21 velas de 15m = 63 velas de 5m

        $candlesBufferArray = [];

        foreach(array_chunk($building_candles, 3) as $chunk){
            $n_candle = [
                "open" => $chunk[2]['open'],
                "high" => max([$chunk[0]['high'],
                                $chunk[1]['high'],
                                $chunk[2]['high']
                                ]),
                "low" => min([$chunk[0]['low'],
                                $chunk[1]['low'],
                                $chunk[2]['low']
                                ]),
                "close" => $chunk[0]['close']
            ];
            $candlesBufferArray[] = $n_candle;

        }

        return $candlesBufferArray; 
    }



    public function ATRstopIndicator(): float {
        
        #   importar valores de indicators.json


        $last_atrx = $this->last_indicator_values->last_atr_stop;



        #   Calculo de ATR Trailing Stop

        $highs = array_reverse(array_slice($this->candle_data['highs'], 0, $this->config->indicator_inputs->atr_length + 1));
        $lows = array_reverse(array_slice($this->candle_data['lows'], 0, $this->config->indicator_inputs->atr_length + 1));
        $closes = array_reverse(array_slice($this->candle_data['closes'], 0, $this->config->indicator_inputs->atr_length + 1));
        
        $atr = trader_atr($highs, $lows, $closes, $this->config->indicator_inputs->atr_length);
        $atr = array_pop($atr);


        $nLoss = $atr * $this->config->indicator_inputs->atr_stop_mult;

        if($this->close > $last_atrx && $this->previous_close > $last_atrx)
        {
            $ATRx = max($last_atrx, $this->close - $nLoss);
        }
        else if($this->close < $last_atrx && $this->previous_close < $last_atrx)
        {
            $ATRx = min($last_atrx, $this->close + $nLoss);
        }
        else if($this->close > $last_atrx)
        {
            $ATRx = $this->close - $nLoss;
        }
        else
        {
            $ATRx = $this->close + $nLoss;
        }

        echo "ATR STOP: " . $ATRx . PHP_EOL;
        return $ATRx;
    }


    private function ImportLastIndicatorValues(){

        $this->last_indicator_values = json_decode(file_get_contents(self::INDICATOR_VALUES));

    }


    public function UpdateIndicatorValues($value)
    {
        $this->last_indicator_values->last_atr_stop = $value; 
        file_put_contents(self::INDICATOR_VALUES, json_encode($this->last_indicator_values, JSON_PRETTY_PRINT));

    }

}
