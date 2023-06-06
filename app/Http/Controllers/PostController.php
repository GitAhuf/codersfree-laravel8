<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http
;
class PostController extends Controller
{
    public function store(){

        $response = Http::withHeaders([
            'Accept' => 'aplication/json',
            'Authorization' => 'Bearer ' . auth()->user()->accessToken->access_token
        ])->post('http://api.codersfree.test/v1/posts', [
            'name' => 'Este es un nombre de prueba',
            'slug' => 'este-es-nombre-prueba',
            'extract' => 'dskjfhsdk',
            'body' => 'dfnkfsnkddsfsdfsd',
            'category_id' => 1            
        ]);

        return $response->json();
    }
}
