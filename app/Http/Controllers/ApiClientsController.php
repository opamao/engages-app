<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Clients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiClientsController extends Controller
{
    public function postRegister(Request $request)
    {

        if (empty($request->nom)) {
            return response()->json([
                'message' => 'Votre nom est obligatoire.',
            ], 422);
        }
        if (empty($request->prenom)) {
            return response()->json([
                'message' => 'Votre prénom est obligatoire.',
            ], 422);
        }
        if (empty($request->tel)) {
            return response()->json([
                'message' => 'Votre numéro de téléphone est obligatoire.',
            ], 422);
        }
        if (empty($request->email)) {
            return response()->json([
                'message' => 'Votre adresse email est obligatoire.',
            ], 422);
        }
        if (empty($request->password)) {
            return response()->json([
                'message' => 'Votre mot de passe est obligatoire.',
            ], 422);
        }

        // Vérifie si l'email existe déjà
        $user = Clients::where('email_client', $request->email)->first();
        if ($user) {
            return response()->json([
                'message' => 'L\'adresse email existe déjà.',
            ], 422);
        }
        // Vérifie si le numéro de téléphone existe déjà
        $user = Clients::where('telephone_client', $request->tel)->first();
        if ($user) {
            return response()->json([
                'message' => 'Le numéro de téléphone existe déjà.',
            ], 422);
        }

        $client = new Clients();
        $client->id_client = Str::uuid();
        $client->nom_client = $request->nom;
        $client->prenom_client = $request->prenom;
        $client->telephone_client = $request->tel;
        $client->email_client = $request->email;
        $client->password_client = Hash::make($request->password);
        $client->save();

        return response()->json($client);
    }

    public function postLogin(Request $request)
    {

        if (empty($request->login)) {
            return response()->json([
                'message' => 'Votre identifiant est obligatoire.',
            ], 422);
        }
        if (empty($request->password)) {
            return response()->json([
                'message' => 'Votre mot de passe est obligatoire.',
            ], 422);
        }

        $user = Clients::where('email_client', $request->login)
            ->orWhere('telephone_client', $request->login)
            ->first();
        if ($user && Hash::check($request->password, $user->password_client)) {

            // L'utilisateur est connecté
            // $token = $user->createToken('API_KEY')->plainTextToken;

            return response()->json([
                'status' => $user->status_client,
                'objet' => [
                    'identifiant' => $user->id_client,
                    'nom' => $user->nom_client,
                    'prenom' => $user->prenom_client,
                    'tel' => $user->telephone_client,
                    'email' => $user->email_client,
                    'photo' => $user->photo_client,
                ]
            ], 200);
        } else {

            // Identifiant incorrect
            return response()->json([
                'message' => 'Les identifiants sont incorrects.',
            ], 401);
        }
    }

    public function getForgot()
    {
    }

    public function getOtp()
    {
    }

    public function postNewPassword()
    {
    }
}
