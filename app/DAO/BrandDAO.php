<?php
namespace App\DAO;

use DB;
use App\Utils\RPC;
use App\Brand;

use Request;

class BrandDAO
{
    public static function lists($num = 0) {
        if($num == 0) {
            $brand = Brand::get();
        } else {
            $brand = Brand::paginate($num);
        }
        return $brand;
    }
}