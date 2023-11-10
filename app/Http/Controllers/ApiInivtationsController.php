<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Besoins;
use App\Models\Contacts;
use App\Models\Galeries;
use App\Models\Informations;
use App\Models\Invitations;
use App\Models\Programmes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiInivtationsController extends Controller
{
    public function createMariage(Request $request)
    {

        $informations = new Informations();
        $informations->id_info = Str::uuid();
        $informations->prenom_garcon = $request->prenomGracon;
        $informations->prenom_fille = $request->prenomFille;
        $informations->message = $request->message;
        $informations->date_mariage = $request->dateMariage;
        $informations->couleur = $request->couleur;
        $informations->client_id = $request->client;

        if (!$informations->save()) {
            return response()->json([
                'message' => "Problème lors de la création de la partie information. Vous pouvez continuer dans historique",
            ], 401);
        }

        foreach ($request->programmes as $programme) {
            $programe = new Programmes();
            $programe->id_prog = Str::uuid();
            $programe->titre_pro = $programme['titreProg'];
            $programe->lieu_pro = $programme['lieuPro'];
            $programe->date_pro = $programme['datePro'];
            $programe->client_id = $programme['client'];

            if (!$programe->save()) {
                return response()->json([
                    'message' => "Problème lors de la création du programme " . $programme['titreProg'] . ". Vous pouvez continuer dans historique",
                ], 401);
            }
        }

        // decrypt image base64 data
        foreach ($request->galeries as $key => $galerie) {
            $imageData = str_replace('data:image/png;base64,', '', $galerie['photoGal']);
            $imageData = str_replace(' ', '+', $imageData);
            $imageData = base64_decode($imageData);

            $imageName = 'galerie_image_' . time() . '_' . $key . '.png';

            file_put_contents(public_path('galeries/' . $imageName), $imageData);

            $galeri = new Galeries();
            $galeri->id_gal = Str::uuid();
            $galeri->photo_gal = $imageName;
            $galeri->libelle_gal = $galerie['libelleGal'];
            $galeri->type_gal = $galerie['typeGal'];
            $galeri->client_id = $galerie['client'];

            if (!$galeri->save()) {
                return response()->json([
                    'message' => "Problème lors de la création de la partie galerie. Vous pouvez continuer dans historique",
                ], 401);
            }
        }

        foreach ($request->contacts as $contact) {
            $contac = new Contacts();
            $contac->id_cont = Str::uuid();
            $contac->nom_cont = $contact['nomCont'];
            $contac->tel_cont = $contact['telCont'];
            $contac->client_id = $contact['client'];

            if (!$contac->save()) {
                return response()->json([
                    'message' => "Problème lors de la création de la partie contact. Vous pouvez continuer dans historique",
                ], 401);
            }
        }

        foreach ($request->besoins as $besoin) {
            $imageData = str_replace('data:image/png;base64,', '', $galerie['photoBeso']);
            $imageData = str_replace(' ', '+', $imageData);
            $imageData = base64_decode($imageData);

            $imageName = 'besoin_image_' . time() . '_' . $key . '.png';

            file_put_contents(public_path('besoins/' . $imageName), $imageData);

            $besoi = new Besoins();
            $besoi->id_beso = Str::uuid();
            $besoi->photo_beso = $imageName;
            $besoi->libelle_beso = $besoin['libelleBeso'];
            $besoi->prix_beso = $besoin['prixBeso'];
            $besoi->type_beso = $besoin['typeBeso'];
            $besoi->client_id = $besoin['client'];

            if (!$besoi->save()) {
                return response()->json([
                    'message' => "Problème lors de la création de la partie besoins. Vous pouvez continuer dans historique",
                ], 401);
            }
        }

        foreach ($request->invitations as $invitation) {
            $invitatio = new Invitations();
            $invitatio->id_inv = Str::uuid();
            $invitatio->contact_inv = $invitation['contactInv'];
            $invitatio->type_inv = $invitation['typeInv'];
            $invitatio->client_id = $invitation['client'];

            if (!$invitatio->save()) {
                return response()->json([
                    'message' => "Problème lors de la création de la partie invitation. Vous pouvez continuer dans historique",
                ], 401);
            }
        }

        return response()->json([
            'message' => "Votre carte d'invitation a été envoyée",
        ], 200);
    }

    public function getInvitation($id)
    {
    }
}
