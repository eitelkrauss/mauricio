<?php

require 'vendor/autoload.php';

date_default_timezone_set("UTC");

$bot = new App\Bot();

$size = 1;

sleep(15);


if($bot->GetSignal() == "BUY"){
    echo "BUY signal confirmed" . PHP_EOL;
    if($bot->CheckPositions()){
        $bot->closePosition(NULL);
        echo "Closing short position" . PHP_EOL;
    }    
    PlaceOrder($bot->Buy($size))
        ->then(
            function($order) use ($bot, $size){
                $bot->CloseLong($size);
                echo "Orders went through. TP order placed" . PHP_EOL;
            },
            function(Exception $exception){
                echo $exception->getMessage() . PHP_EOL;
            });
}
elseif($bot->GetSignal() == "SELL"){
    echo "SELL signal confirmed" . PHP_EOL;
    if($bot->CheckPositions()){
        $bot->closePosition(NULL);
        echo "Closing long position" . PHP_EOL;
    }
    PlaceOrder($bot->Sell($size))
        ->then(
            function($order) use ($bot, $size){
                $bot->CloseShort($size);
                echo "Orders went through. TP order placed" . PHP_EOL;
            },
            function(Exception $exception){
                echo $exception->getMessage() . PHP_EOL;
            });

} else {
    echo $bot->CheckPositions() ? "Active open position!" . PHP_EOL : "No signal" . PHP_EOL;
}

echo "CLOSE: " . $bot->close . PHP_EOL;
$bot->UpdateIndicatorValues($bot->ATRstopIndicator());
echo date("l jS \of F Y H:i:s"). " UTC" . PHP_EOL;
echo PHP_EOL;






function PlaceOrder($order){

    $deferred = new \React\Promise\Deferred();

    if($order){
        $deferred->resolve($order);
    } else {
        $deferred->reject(new Exception("Order failed"));
    }

    return $deferred->promise();
}

