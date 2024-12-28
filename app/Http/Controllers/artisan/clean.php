<?php

namespace App\Http\Controllers\artisan;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class clean extends Controller
{
    //Create Clean routs
    public function ClearCache(){
        Artisan::call('config:clear');
        Artisan::Call('route:clear');
        Artisan::Call('cache:clear');
        Artisan::Call('view:clear');

        // Retuen Response
        return response() -> json([
            'message' => 'Cleaed the cach successfuly'
        ], 201);
    }
}
