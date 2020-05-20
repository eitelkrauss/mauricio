<?php

require 'vendor/autoload.php';

$bitmex = new App\BitMex ("zitINFv608cwQAWJQumd8YGy", "6swulefXUpsXhhZRt3ql2LbVWMMKP2HdKTXsS6PbulxgZTDu");


var_dump($bitmex->closePosition(NULL));
