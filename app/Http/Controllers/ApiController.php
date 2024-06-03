<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    public $client;
    public function __construct(){
        $this ->client = new Client([
            'base_uri' => 'https://erp.nttek.ru/api/schedule/legacy/',
            'timeout'  => 1.0,
        ]);
    }

    public static function get_date(){
        $import = new ApiController();
        try{
            $response = $import->client->request('GET', '');
        } catch(\Exception $e){
            return Log::channel("telegram")->info("Ошибка подключения");
        }
        $data = (json_decode($response->getBody()));

        $newArray = [];
        $counter = 0;

        foreach ($data as $value) {
            if ($counter < 5) {
                $date = implode('-', array_reverse(explode('.', $value)));
                $newArray[] = $date;
                $counter++;
            } else {
                break;
            }
        }

        return $newArray;
    }
}
