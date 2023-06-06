<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;


use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // VALIDA EMAIL Y CONTRASEÑA
        $request->validate(
            [
            'email' => 'required|string|email',
            'password' => 'required|string'
            ]);

            // PETICION A LA API
            $response = Http::withHeaders([
                'Acept' => 'aplication/json'
            ])->post('http://api.codersfree.test/v1/login', [
                'email' => $request->email,
                'password' => $request->password
            ]
        );

        // VERIFICA EL STATUS DIFERENTE A 404 0 DEVUELVE Y MUESTRA ERROR
        if($response->status() == 404){
            return back()->withErrors('These credentials do not match our records.');
        }

        // ALMACENA LA RESPUESTA JSON
        $service = $response->json();

        // UTILIZA LA INFO PARA ACTUALIZAR O CREAR REGISTRO
        $user = User::updateOrCreate([
           'email' => $request->email 
        ], $service['data']);

        if(!$user->accessTokenphp){
            // PETICION HTTP PARA ACCESS TOKEN
            $response = Http::withHeaders([
                'Accept' => 'aplication/json'
            ])->post('http://api.codersfree.test/oauth/token', [
                'grant_type' => 'password',
                'client_id' => '9957a452-c219-4167-b02a-036d97716cc8',
                'client_secret' => 'ues1nC0PFXVheXv7hitiVb3lU1J2XjGZjw9wJ8JI',
                'username' => $request->email,
                'password' => $request->password,
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

        Auth::login($user, $request->remember);
        
        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
