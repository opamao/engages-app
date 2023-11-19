<?php

namespace App\Http\Controllers;

use App\Constants;
use App\Http\Controllers\Controller;
use App\Models\Besoins;
use App\Models\Contacts;
use App\Models\Galeries;
use App\Models\Informations;
use App\Models\Invitations;
use App\Models\Programmes;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ApiInivtationsController extends Controller
{
    public function createMariage(Request $request)
    {

        $codeMariage = Carbon::now()->format('YmdHis');

        $informations = new Informations();
        $informations->id_info = Str::uuid();
        $informations->prenom_garcon = $request->prenomGracon;
        $informations->prenom_fille = $request->prenomFille;
        $informations->message = $request->message;
        $informations->date_mariage = $request->dateMariage;
        $informations->couleur = $request->couleur;
        $informations->code_mariage = 'EA-' . $codeMariage;
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
            $programe->info_id = $informations->id_info;

            if (!$programe->save()) {
                return response()->json([
                    'message' => "Problème lors de la création du programme " . 'titreProg' . ". Vous pouvez continuer dans historique",
                ], 401);
            }
        }

        // decrypt image base64 data

        for ($i = 0; $i < count($request->galeries); $i++) {
            $imageData = $request->galeries[$i];
            // Enlever la partie "data:image/png;base64,"
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            // Décoder la chaîne base64
            $imageDecoded = base64_decode($imageData);
            // Obtenir le MIME type
            $finfo = finfo_open();
            $mimeType = finfo_buffer($finfo, $imageDecoded, FILEINFO_MIME_TYPE);
            finfo_close($finfo);
            // Extraire l'extension
            $extension = explode('/', $mimeType)[1];

            $imageName = 'galerie_image_' . time() . '_' . '.' . $extension;

            file_put_contents(public_path('galeries/' . $imageName), $imageDecoded);

            $galeri = new Galeries();
            $galeri->id_gal = Str::uuid();
            $galeri->photo_gal = $imageName;
            $galeri->libelle_gal = $request->prenomGracon . ' & ' . $request->prenomFille;
            $galeri->type_gal = 'mariage';
            $galeri->info_id = $informations->id_info;

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
            $contac->info_id = $informations->id_info;

            if (!$contac->save()) {
                return response()->json([
                    'message' => "Problème lors de la création de la partie contact. Vous pouvez continuer dans historique",
                ], 401);
            }
        }

        if (!empty($request->besoins)) {
            foreach ($request->besoins as $besoin) {
                $besoi = new Besoins();
                $besoi->id_beso = Str::uuid();
                $besoi->libelle_beso = $besoin['libelleBeso'];
                $besoi->prix_beso = $besoin['prixBeso'];
                $besoi->type_beso = $besoin['typeBeso'];
                $besoi->info_id = $informations->id_info;

                if (!$besoi->save()) {
                    return response()->json([
                        'message' => "Problème lors de la création de la partie besoins. Vous pouvez continuer dans historique",
                    ], 401);
                }
            }
        }

        for ($i = 0; $i < count($request->invitations); $i++) {
            $invitatio = new Invitations();
            $invitatio->id_inv = Str::uuid();
            $invitatio->client_inv = $request->invitations[$i];
            $invitatio->type_inv = 'mariage';
            $invitatio->info_id = $informations->id_info;

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
        $baseBesoinsPath = Constants::$urlBesoinsBase;
        $baseGaleriesPath = Constants::$urlGaleriesBase;

        $info = Informations::join("invitations", "invitations.info_id", "=", "informations.id_info")
            ->select(
                "informations.id_info",
                "informations.prenom_garcon",
                "informations.prenom_fille",
                "informations.message",
                "informations.date_mariage",
                "informations.couleur",
                "invitations.id_inv",
                "invitations.type_inv",
                "invitations.etat_inv",
            )
            ->where("invitations.contact_inv", $id)
            ->first();

        $prog = Programmes::where("info_id", $info->id_info)
            ->select(
                "programmes.id_prog",
                "programmes.titre_pro",
                "programmes.lieu_pro",
                "programmes.date_pro",
            )
            ->get();

        $gal = Galeries::where("info_id", $info->id_info)
            ->select(
                "galeries.id_gal",
                "galeries.photo_gal",
                "galeries.libelle_gal",
                "galeries.type_gal",
            )
            ->get();

        foreach ($gal as $galerie) {
            $galerie->photo_gal = $baseGaleriesPath . $galerie->photo_gal;
        }

        $pers = Contacts::where("info_id", $info->id_info)
            ->select(
                "personne_contact.id_cont",
                "personne_contact.nom_cont",
                "personne_contact.tel_cont",
            )
            ->get();

        $bes = Besoins::where("info_id", $info->id_info)
            ->select(
                "besoins.id_beso",
                "besoins.photo_beso",
                "besoins.libelle_beso",
                "besoins.prix_beso",
                "besoins.type_beso",
                "besoins.statut_beso"
            )
            ->get();

        foreach ($bes as $besoin) {
            $besoin->photo_beso = $baseBesoinsPath . $besoin->photo_beso;
        }

        if ($info) {

            return response()->json([
                'informations' => $info,
                'objet' => [
                    'programmes' => $prog,
                    'galeries' => $gal,
                    'personnes' => $pers,
                    'besoins' => $bes,
                ],
            ], 200);
        } else {
            return response()->json([
                'message' => "Pas d'invitation disponible",
            ], 401);
        }
    }
}
