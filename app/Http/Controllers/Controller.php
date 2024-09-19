<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Http;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function fetchFromOdds($url, $query)
    {
        $api_key = env("ODD_API_KEY", '');
        $api_url = env("ODD_API_URL", '');

        $response = Http::get($api_url.'/v4'.$url.'/?'.$query.'&apiKey='.$api_key);
        if ( $response->successful() ) {
            return $response->body();
        }

        return null;
    }
    
}
