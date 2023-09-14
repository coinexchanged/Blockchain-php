<?php

namespace App\Console\Commands;

use App\Market;
use App\Utils\RPC;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GetMarket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_market';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取行情';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
	 
	
    public function handle()
    {
      //  $coin_list = RPC::apihttp("https://api.coinmarketcap.com/v2/ticker/?limit=20");
      //$coin_list = file_get_contents("https://api.coinmarketcap.com/v2/ticker/?limit=20");
        //$coin_list = @json_decode($coin_list,true);
		$opts = [
			"http" => [
				"method" => "GET",
				"header" => "Accepts: application/json\r\n" .
					"X-CMC_PRO_API_KEY: 8c89b9cf-8fcb-4f2a-bac1-c93295b72074\r\n"
			]
		];

		$context = stream_context_create($opts);

		// Open the file using the HTTP headers set above
		$file = file_get_contents('https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest?id=1,2', false, $context);
		$coin_list = json_decode($file,true);
        DB::beginTransaction();
        try {

            if (!empty($coin_list['data'])) {
                foreach ($coin_list['data'] as $row) {
                    $market = Market::find($row['id']);
                    if (empty($market)) {
                        $market = new Market();
                    }
                    $market->id                 = $row['id'];
                    $market->name               = $row['name'];
                    $market->symbol             = $row['symbol'];
                   // $market->website_slug       = $row['website_slug'];
                    $market->rank               = $row['cmc_rank'];
                    $market->circulating_supply = $row['circulating_supply'];
                    $market->total_supply       = $row['total_supply'];
                    $market->max_supply         = $row['max_supply'];
                    $market->quotes             = serialize($row['quote']);
                    $market->last_updated       = $row['last_updated'];
                    $market->save();
                }
                DB::commit();
                echo(111);
                $message = '请求接口成功，并更新数据库->'.date('Y-m-d H:i:s');

                $this->info($message);
            }else{
                echo(222);
                $message = '请求数据接口失败，无数据->'.date('Y-m-d H:i:s');

                $this->info($message);
            }


        }catch (\Exception $exception){
            DB::rollback();
            echo(333);
            $message = $exception->getMessage().'->'.date('Y-m-d H:i:s');

            $this->info($message);
        }
    }
}
