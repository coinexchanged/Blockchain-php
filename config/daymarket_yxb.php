<?php
return [
    "libra_data"=>[
        "type"=> "daymarket",
        "period"=>"1min",
        "match_id"=> 31,
        "currency_id"=> 29,
        "currency_name"=> "YXB",
        "legal_id"=> 3,
        "legal_name"=> "USDT",
        "open"=>mt_rand(9581,9588)/10000,
        "close"=>mt_rand(9581,9588)/10000,
        "high"=>mt_rand(9581,9588)/10000,
        "low"=>mt_rand(9581,9588)/10000,
        "symbol"=>"YXB/USDT",
        "volume"=> 2755.89726152034,
        "time"=> time(),
        "change"=> "-0.35",
        "now_price"=>mt_rand(9581,9588)/10000,
        "api_form"=> "huobi_websocket"
    ]
];