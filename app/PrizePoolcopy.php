<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Dao\PrizePool\PrizeSender;
use App\DAO\PrizePool\PrizeReceiver;
use App\DAO\PrizePool\PrizeCalculator;

class PrizePoolcopy extends Model
{

    protected $table = 'prize_pool';
    public $timestamps = false;

}
