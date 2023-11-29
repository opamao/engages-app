<?php

namespace App\Http\Controllers;

use App\Constants;
use App\Http\Controllers\Controller;
use App\Mail\EngagesInvitation;
use App\Models\Besoins;
use App\Models\Clients;
use App\Models\Contacts;
use App\Models\Galeries;
use App\Models\Informations;
use App\Models\Invitations;
use App\Models\Programmes;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class ApiInivtationsController extends Controller
{
    // Création des invitations
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
        $informations->code_mariage = 'EAMA' . $codeMariage;
        $informations->client_id = $request->client;

        if (!$informations->save()) {
            return response()->json([
                'message' => "Impossible de créer l'invitation. Veuillez réessayer",
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
                    'message' => "Problème lors de la création du programme. Vous pouvez continuer dans historique",
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

            file_put_contents(public_path('mariages/' . $imageName), $imageDecoded);

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

        $numero = [];
        for ($i = 0; $i < count($request->invitations); $i++) {

            $verifInvitation = Clients::where('telephone_client', $request->invitations[$i])->first();

            if ($verifInvitation) {
                $invitatio = new Invitations();
                $invitatio->id_inv = Str::uuid();
                $invitatio->client_inv = $verifInvitation->id_client;
                $invitatio->type_inv = 'mariage';
                $invitatio->info_id = $informations->id_info;

                if (!$invitatio->save()) {
                    return response()->json([
                        'message' => "Impossible d'envoyer une invitation à " . $request->invitations[$i] . ". Vous pouvez continuer dans historique",
                    ], 401);
                }

                $mailContenu = [
                    'intitule' => $informations->prenomGracon . ' & ' . $informations->prenomFille . ' vous invites a leur mariage.',
                    'title' => $informations->code_mariage,
                    'body' => $request->message,
                ];
                Mail::to($verifInvitation->email_client)->send(new EngagesInvitation($mailContenu));
            } else {
                $numero[] = $request->invitations[$i];
            }
        }

        return response()->json([
            'message' => "Votre carte d'invitation a été envoyée",
            'numero' => $numero,
            'code' => $informations->code_mariage,
        ], 200);
    }
    public function createAnniversaire(Request $request)
    {

        $codeMariage = Carbon::now()->format('YmdHis');

        $informations = new Informations();
        $informations->id_info = Str::uuid();
        $informations->prenom_garcon = $request->prenomGracon; // nom de la paersonne concernée
        $informations->message = $request->message;
        $informations->date_mariage = $request->dateMariage; // date de l'anniversaire
        $informations->couleur = $request->couleur;
        $informations->code_mariage = 'EAAN' . $codeMariage;
        $informations->client_id = $request->client;

        if (!$informations->save()) {
            return response()->json([
                'message' => "Impossible de créer l'invitation. Veuillez réessayer!",
            ], 401);
        }

        foreach ($request->programmes as $programme) {
            $programe = new Programmes();
            $programe->id_prog = Str::uuid();
            $programe->lieu_pro = $programme['lieuPro'];
            $programe->date_pro = $programme['datePro'];
            $programe->info_id = $informations->id_info;

            if (!$programe->save()) {
                return response()->json([
                    'message' => "Problème lors de la création du programme. Vous pouvez continuer dans historique",
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

            file_put_contents(public_path('anniversaires/' . $imageName), $imageDecoded);

            $galeri = new Galeries();
            $galeri->id_gal = Str::uuid();
            $galeri->photo_gal = $imageName;
            $galeri->libelle_gal = $request->prenomGracon;
            $galeri->type_gal = 'anniversaire';
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

        $numero = [];
        for ($i = 0; $i < count($request->invitations); $i++) {

            $verifInvitation = Clients::where('telephone_client', $request->invitations[$i])->first();

            if ($verifInvitation) {
                $invitatio = new Invitations();
                $invitatio->id_inv = Str::uuid();
                $invitatio->client_inv = $verifInvitation->id_client;
                $invitatio->type_inv = 'anniversaire';
                $invitatio->info_id = $informations->id_info;

                if (!$invitatio->save()) {
                    return response()->json([
                        'message' => "Impossible d'envoyer une invitation à " . $request->invitations[$i] . ". Vous pouvez continuer dans historique",
                    ], 401);
                }

                $mailContenu = [
                    'intitule' => $request->prenomGracon . ' vous invites a son anniversaire.',
                    'title' => $informations->code_mariage,
                    'body' => $request->message,
                ];
                Mail::to($verifInvitation->email_client)->send(new EngagesInvitation($mailContenu));
            } else {
                $numero[] = $request->invitations[$i];
            }
        }

        return response()->json([
            'message' => "Votre carte d'invitation a été envoyée",
            'numero' => $numero,
            'code' => $informations->code_mariage,
        ], 200);
    }
    public function createBapteme(Request $request)
    {

        $codeMariage = Carbon::now()->format('YmdHis');

        $informations = new Informations();
        $informations->id_info = Str::uuid();
        $informations->prenom_garcon = $request->prenomGracon;
        $informations->message = $request->message;
        $informations->date_mariage = $request->dateMariage;
        $informations->couleur = $request->couleur;
        $informations->code_mariage = 'EABA' . $codeMariage;
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
                    'message' => "Problème lors de la création du programme. Vous pouvez continuer dans historique",
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

            file_put_contents(public_path('baptemes/' . $imageName), $imageDecoded);

            $galeri = new Galeries();
            $galeri->id_gal = Str::uuid();
            $galeri->photo_gal = $imageName;
            $galeri->libelle_gal = $request->prenomGracon;
            $galeri->type_gal = 'bapteme';
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

        $numero = [];
        for ($i = 0; $i < count($request->invitations); $i++) {

            $verifInvitation = Clients::where('telephone_client', $request->invitations[$i])->first();

            if ($verifInvitation) {
                $invitatio = new Invitations();
                $invitatio->id_inv = Str::uuid();
                $invitatio->client_inv = $verifInvitation->id_client;
                $invitatio->type_inv = 'bapteme';
                $invitatio->info_id = $informations->id_info;

                if (!$invitatio->save()) {
                    return response()->json([
                        'message' => "Impossible d'envoyer une invitation à " . $request->invitations[$i] . ". Vous pouvez continuer dans historique",
                    ], 401);
                }

                $mailContenu = [
                    'intitule' => $request->prenomGracon . ' vous invites a son baptême.',
                    'title' => $informations->code_mariage,
                    'body' => $request->message,
                ];
                Mail::to($verifInvitation->email_client)->send(new EngagesInvitation($mailContenu));
            } else {
                $numero[] = $request->invitations[$i];
            }
        }

        return response()->json([
            'message' => "Votre carte d'invitation a été envoyée",
            'numero' => $numero,
            'code' => $informations->code_mariage,
        ], 200);
    }
    public function createNaissance(Request $request)
    {

        $codeMariage = Carbon::now()->format('YmdHis');

        $informations = new Informations();
        $informations->id_info = Str::uuid();
        $informations->prenom_garcon = $request->prenomGracon;
        $informations->prenom_fille = $request->prenomFille;
        $informations->message = $request->message;
        $informations->date_mariage = $request->dateMariage;
        $informations->couleur = $request->couleur;
        $informations->code_mariage = 'EANA' . $codeMariage;
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
                    'message' => "Problème lors de la création du programme. Vous pouvez continuer dans historique",
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

            file_put_contents(public_path('naissances/' . $imageName), $imageDecoded);

            $galeri = new Galeries();
            $galeri->id_gal = Str::uuid();
            $galeri->photo_gal = $imageName;
            $galeri->libelle_gal = $request->prenomGracon . ' & ' . $request->prenomFille;
            $galeri->type_gal = 'naissance';
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

        $numero = [];
        for ($i = 0; $i < count($request->invitations); $i++) {

            $verifInvitation = Clients::where('telephone_client', $request->invitations[$i])->first();

            if ($verifInvitation) {
                $invitatio = new Invitations();
                $invitatio->id_inv = Str::uuid();
                $invitatio->client_inv = $verifInvitation->id_client;
                $invitatio->type_inv = 'naissance';
                $invitatio->info_id = $informations->id_info;

                if (!$invitatio->save()) {
                    return response()->json([
                        'message' => "Impossible d'envoyer une invitation à " . $request->invitations[$i] . ". Vous pouvez continuer dans historique",
                    ], 401);
                }

                $mailContenu = [
                    'intitule' => $request->prenomGracon . ' & '. $request->prenomFille . ' vous invites informe de la naissance de leur bébé.',
                    'title' => $informations->code_mariage,
                    'body' => $request->message,
                ];
                Mail::to($verifInvitation->email_client)->send(new EngagesInvitation($mailContenu));
            } else {
                $numero[] = $request->invitations[$i];
            }
        }

        return response()->json([
            'message' => "Votre carte d'invitation a été envoyée",
            'numero' => $numero,
            'code' => $informations->code_mariage,
        ], 200);
    }
    public function createAutre(Request $request)
    {

        $codeMariage = Carbon::now()->format('YmdHis');

        $informations = new Informations();
        $informations->id_info = Str::uuid();
        $informations->prenom_garcon = $request->prenomGracon; // nom de l'évènement
        $informations->message = $request->message; // description de l'évènement
        $informations->date_mariage = $request->dateMariage;
        $informations->couleur = $request->couleur;
        $informations->code_mariage = 'EAAU' . $codeMariage;
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
                    'message' => "Problème lors de la création du programme. Vous pouvez continuer dans historique",
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

            file_put_contents(public_path('autres/' . $imageName), $imageDecoded);

            $galeri = new Galeries();
            $galeri->id_gal = Str::uuid();
            $galeri->photo_gal = $imageName;
            $galeri->libelle_gal = $request->prenomGracon;
            $galeri->type_gal = 'autre';
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

        $numero = [];
        for ($i = 0; $i < count($request->invitations); $i++) {

            $verifInvitation = Clients::where('telephone_client', $request->invitations[$i])->first();

            if ($verifInvitation) {
                $invitatio = new Invitations();
                $invitatio->id_inv = Str::uuid();
                $invitatio->client_inv = $verifInvitation->id_client;
                $invitatio->type_inv = 'autres';
                $invitatio->info_id = $informations->id_info;

                if (!$invitatio->save()) {
                    return response()->json([
                        'message' => "Impossible d'envoyer une invitation à " . $request->invitations[$i] . ". Vous pouvez continuer dans historique",
                    ], 401);
                }

                $mailContenu = [
                    'intitule' => $request->prenomGracon . ' vous invites a un grand évènement.',
                    'title' => $informations->code_mariage,
                    'body' => $request->message,
                ];
                Mail::to($verifInvitation->email_client)->send(new EngagesInvitation($mailContenu));
            } else {
                $numero[] = $request->invitations[$i];
            }
        }

        return response()->json([
            'message' => "Votre carte d'invitation a été envoyée",
            'numero' => $numero,
            'code' => $informations->code_mariage,
        ], 200);
    }

    // liste invitation pour invité
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
            ->where("invitations.client_inv", $id)
            ->where("invitations.type_inv", "mariage")
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
    public function getAnniversaire($id)
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
            ->where("invitations.client_inv", $id)
            ->where("invitations.type_inv", "anniversaire")
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
    public function getBapteme($id)
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
            ->where("invitations.client_inv", $id)
            ->where("invitations.type_inv", "bapteme")
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
    public function getNaissance($id)
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
            ->where("invitations.client_inv", $id)
            ->where("invitations.type_inv", "naissance")
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
    public function getAutre($id)
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
            ->where("invitations.client_inv", $id)
            ->where("invitations.type_inv", "autre")
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
    // liste invitation pour créateur
    public function getInvitationMariage($id)
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
            ->where("informations.client_id", $id)
            ->where("invitations.type_inv", "mariage")
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
    public function getInvitationAnniversaire($id)
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
            ->where("informations.client_id", $id)
            ->where("invitations.type_inv", "anniversaire")
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
    public function getInvitationBapteme($id)
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
            ->where("informations.client_id", $id)
            ->where("invitations.type_inv", "bapteme")
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
    public function getInvitationNaissance($id)
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
            ->where("informations.client_id", $id)
            ->where("invitations.type_inv", "naissance")
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
    public function getInvitationAutres($id)
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
            ->where("informations.client_id", $id)
            ->where("invitations.type_inv", "autre")
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

    // permet d'accepter ou refuser une invitation
    public function postEtatInvitation(Request $request)
    {
        // 1 = accepte; 2 = refuse
        Invitations::where('id_inv', $request->idInv)
            ->where('cleint_id', $request->id)
            ->update([
                'etat_inv' => $request->etat == 1 ? 'accepte' : 'refuse',
            ]);

        if ($request->etat == 1) {
            return response()->json([
                'message' => "Votre invitation a été accepté"
            ], 200);
        } else {
            return response()->json([
                'message' => 'Votre invitation a été réfusé'
            ], 200);
        }
    }

    // permet d'integrer une invitation
    public function getIntegration($code, $id, $type)
    {

        $verif = Informations::join("invitations", "invitations.info_id", "=", "informations.id_info")
            ->where("informations.code_mariage", $code)
            ->where("invitations.client_inv", $id)
            ->first();

        if ($verif) {
            return response()->json([
                'message' => "Vous avez déjà intégrer l'invitation",
            ], 401);
        } else {

            $verifInfo = Informations::where("code_mariage", $code)
                ->first();

            if ($verifInfo) {

                $invitatio = new Invitations();
                $invitatio->id_inv = Str::uuid();
                $invitatio->client_inv = $id;
                $invitatio->type_inv = $type;
                $invitatio->etat_inv = "accepte";
                $invitatio->info_id = $verifInfo->id_info;

                if (!$invitatio->save()) {
                    return response()->json([
                        'message' => "Impossible d'intégrer l'invitation. Veuillez réessayer!",
                    ], 401);
                }
            } else {
                return response()->json([
                    'message' => "Le code n'existe pas, veuillez réessayer un autre code",
                ], 401);
            }

            return response()->json([
                'message' => "Votre intégration a été prise en compte",
            ], 200);
        }
    }
}
