<?php


namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait Token{
        public function getAccessToken($user, $service){
              // PETICION HTTP PARA ACCESS TOKEN
              $response = Http::withHeaders([
                'Accept' => 'aplication/json'
            ])->post('http://api.codersfree.test/oauth/token', [
                'grant_type' => 'password',
                'client_id' => config('services.codersfree.client_id'),
                'client_secret' => config('services.codersfree.client_secret'),
                'username' => request('email'),
                'password' => request('password'),
            ]);

            // ALMACENA LA INFORMACION
            $access_token = $response->json(); 

            // CREA UN NUEVO REGISTRO EN LA TABLA ACCESS_TOKEN RELACIONANDOLO CON UN DETERMINADO USUARIO
            $user->accessToken()->create([
                'service_id' => $service['data']['id'],
                'access_token' => $access_token['access_token'],
                'refresh_token' => $access_token['refresh_token'],
                'expires_at' => now()->addSecond($access_token['expires_in'])
            ]);
        }
    }