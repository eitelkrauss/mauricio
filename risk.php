<?php


function positionSize(){
        
    global $bitmex, $entry, $stop, $taker_fee;
    $wallet = $bitmex->getWallet();
    $fondos = $wallet["amount"] / 100000000;
    $account_risk_pct = 0.25 / 100;
    $account_risk = $fondos * $entry * $account_risk_pct;     # riesgo de la cuenta = acc risk
    //$account_risk = 2;      # Riesgo de la cuenta en %
    $trade_fees = $stop * $taker_fee / 100;
    $trade_risk = abs($entry - $stop) + $trade_fees;
    $adjusted_size = round($account_risk / $trade_risk, 4); # Trading Fees en consideracion
    $contratos = round($adjusted_size * $entry);

    return $contratos;
}



