<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ApiController extends Controller
{
    public $client;
    public function __construct(){
        $this ->client = new Client([
            'base_uri' => 'https://erp.nttek.ru/api/schedule/legacy/',
            'timeout'  => 1.0,
        ]);
    }
}
