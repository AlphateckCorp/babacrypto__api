<?php

namespace app\helpers;

use app\helpers\CurlHelper;

class CryptoCoins {

    function getList() {
        $result = CurlHelper::get('https://www.cryptocompare.com/api/data/coinlist');
        return json_decode($result, true);
    }

    function getExchanges() {
        $result = CurlHelper::get('https://min-api.cryptocompare.com/data/all/exchanges');
        return json_decode($result, true);
    }

    function getTopExchanges($fromSymbol,$toSymbol) {
        $url = "https://min-api.cryptocompare.com/data/top/exchanges/full?fsym=".$fromSymbol."&tsym=".$toSymbol;
        $result = CurlHelper::get($url);
        return json_decode($result, true);
    }

    function getPrice($fromSymbol,$toSymbol) {
        $url = "https://min-api.cryptocompare.com/data/pricemultifull?fsyms=".$fromSymbol."&tsyms=".$toSymbol;
        $result = CurlHelper::get($url);
        return json_decode($result);
    }
}