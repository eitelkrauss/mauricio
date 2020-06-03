<?php

require 'vendor/autoload.php';

$bot = new App\Bot();

echo "make candles" . PHP_EOL;
var_dump($bot->MakeCandles("5m", 45));


echo "candle data" . PHP_EOL;
var_dump($bot->candle_data);

echo "close" . PHP_EOL;
var_dump($bot->close);
