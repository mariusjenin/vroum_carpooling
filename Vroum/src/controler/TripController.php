<?php

namespace Vroum\Controler;

use Slim\Psr7\Response;
use Vroum\Model\Note;
use Vroum\Model\Notification;
use Vroum\Model\ParticipeATrajet;
use Vroum\Model\Trip;
use Vroum\Model\User;
use Vroum\Model\InterCity;
use Vroum\Model\ListeUtilisateur;
use Vroum\Observer\NotificationObserver;
use Vroum\View\TripView;
use Vroum\VroumApp;

class TripController
{
    public static function createTrip_post($req, $resp, $args)
    {
        $params = (array)$req->getParsedBody();
        $conducteur = ConnectionManager::getInstance()->getIdConnected();

        $dateStart = $params['start-day'] ?? '';
        $timeStart = $params['start-time'] ?? '';

        $dateEnd = $params['end-day'] ?? '';
        $timeEnd = $params['end-time'] ?? '';

        $cityStart = $params['start-town'] ?? '';
        $cityEnd = $params['end-town'] ?? '';

        $private = $params['private'] ?? false;
        $uul = $params['users-user-list'] ?? '';

        $price = $params['price'] ?? '0';

        $maxSeats = $params['max_seats'] ?? '';

        $precisions = $params['precisions'] ?? '';
        $constraints = $params['constraints'] ?? '';

        $intermediates = $params['villeInter'] ?? [];

        try {
            $u = User::find($conducteur);
            if (!$u->voiture)
                throw new \DomainException(json_encode([ 'err' => 'Vous ne pouvez pas proposer de trajets si vous ne possédez pas de véhicule' ]));

            if ($private){
                if (empty($uul))
                    throw new \DomainException(json_encode(['err' => 'Trajet privé sans liste d\'utilisateur spécifiée', 'field' => 'users-user-list-list']));
                if (!ListeUtilisateur::where('idListe', '=', $uul)->where('createur', '=', $conducteur)->exists())
                    throw new \DomainException(json_encode(['err' => 'Liste invalide', 'field' => 'users-user-list-list']));
            }
            
            if (empty($dateStart) || empty($timeStart) || $timeStart === 'n/a')
                throw new \DomainException(json_encode(['err' => 'Les date et heure de départ sont obligatoires', 'field' => 'date_start']));
            if (empty($cityStart = htmlspecialchars($cityStart)) || empty($cityEnd = htmlspecialchars($cityEnd)))
                throw new \DomainException(json_encode(['err' => 'Les villes de départ et d\'arrivée sont obligatoires', 'field' => 'city_start']));
            if (empty($maxSeats))
                throw new \DomainException(json_encode(['err' => 'Nombre de places disponibles non renseigné', 'field' => 'max_seats']));
            if (empty($price))
                throw new \DomainException(json_encode(['err' => 'Prix du voyage non renseigné', 'field' => 'price']));

            if (!($dateStart = \DateTime::createFromFormat('Y-m-d', $dateStart)))
                throw new \DomainException(json_encode(['err' => 'Date de départ invalide', 'field' => 'date_start']));
            $dateEnd = \DateTime::createFromFormat('Y-m-d', $dateEnd) ?: NULL;

            if (($price = (double)filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) <= 0.)
                throw new \DomainException(json_encode(['err' => 'Prix du voyage invalide (doit être supérieur à 0)', 'field' => 'price']));
            if (($maxSeats = (int)filter_var($maxSeats, FILTER_SANITIZE_NUMBER_INT)) < 0)
                throw new \DomainException(json_encode(['err' => 'Impossible d\'accepter un nombre négatif de personnes', 'field' => 'max_seats']));

            list($hourStart, $minStart) = explode(':', $timeStart);

            $hourStart = (int)filter_var($hourStart, FILTER_SANITIZE_NUMBER_INT);
            if ($hourStart < 0 || $hourStart > 23)
                throw new \DomainException(json_encode(['err' => 'Heure de départ invalide (doit être incluse entre 0 et 23)', 'field' => 'time_start']));
            $minStart = (int)filter_var($minStart, FILTER_SANITIZE_NUMBER_INT);
            if ($minStart < 0 || $minStart > 59)
                throw new \DomainException(json_encode(['err' => 'Minutes de départ invalides (doit être inclus entre 0 et 59)', 'field' => 'time_start']));

            $dateStart = $dateStart->setTime($hourStart, $minStart);

            if ($dateStart < \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')))
                throw new \DomainException(json_encode(['err' => 'La date de départ doit être située après maintenant', 'field' => 'date_start']));

            $hourEnd = 0;
            $minEnd = 0;
            if (!empty($timeEnd) && $timeEnd !== 'n/a') {
                list($hourEnd, $minEnd) = explode(':', $timeEnd);

                $hourEnd = (int)filter_var($hourEnd, FILTER_SANITIZE_NUMBER_INT);
                if ($hourEnd < 0 || $hourEnd > 23)
                    throw new \DomainException(json_encode(['err' => 'Heure d\'arrivée invalide (doit être incluse entre 0 et 23)', 'field' => 'time_end']));

                $minEnd = (int)filter_var($minEnd, FILTER_SANITIZE_NUMBER_INT);
                if ($minEnd < 0 || $minEnd > 59)
                    throw new \DomainException(json_encode(['err' => 'Minutes d\'arrivée invalides (doit être inclus entre 0 et 59)', 'field' => 'time_end']));
            }

            if (!is_null($dateEnd) && $dateEnd->setTime($hourEnd, $minEnd) < $dateStart)
                throw new \DomainException(json_encode(['err' => 'La date d\'arrivée doit forcément être après la date de départ', 'field' => 'date_end']));

            $t = new Trip;
            $t->dateD = $dateStart->format('Y-m-d H:i:s');
            $t->dateA = !is_null($dateEnd) ? $dateEnd->format('Y-m-d H:i:s') : NULL;
            $t->conducteur = $conducteur;
            $t->villeD = $cityStart;
            $t->villeA = $cityEnd;
            if ($private)
                $t->listePrivee = $uul;
            $t->prix = $price;
            $t->placeMax = $maxSeats;
            $t->precisionsRDV = htmlspecialchars($precisions);
            $t->precisionsContraintes = htmlspecialchars($constraints);
            $t->save();

            foreach ($intermediates as $city) {
                $c = new InterCity;
                $c->idTrip = $t->idTrajet;
                $c->city = $city;
                $c->save();
            }
        } catch (\DomainException $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $resp->getBody()->write(VroumApp::getInstance()->urlFor("consultTrip", ["id" => $t->idTrajet]));
        return $resp;
    }

    public static function createTrip($req, Response $resp, $args)
    {
        RedirectManager::getInstance()->refreshCookieUrlRedirect($req, $args);
        $iv = new TripView();
        $resp->getBody()->write($iv->renderCreateTrip());
        return $resp;
    }

    public static function searchTrip_post($req, $resp, $args)
    {
        $params = (array)$req->getParsedBody();
        $conducteur = ConnectionManager::getInstance()->getIdConnected();


//         $t = Trip::join('User', 'conducteur', '=', 'idUser')->join('UserScore', 'UserScore.utilisateur', '=', 'Trajet.conducteur')->where('cancelled','=','0');
//         $t = UserScore::join('Trajet', 'UserScore.utilisateur', '=', 'Trajet.conducteur');
        $sqlQuery = Trip::join('User', 'conducteur', '=', 'idUser')->where('cancelled', '=', '0');
        $sqlQuery->where("dateD", ">=", date("Y-m-d H:i:s"));
        //Filtrement des trajets
        if (array_key_exists("start-day", $params)) {
            $minD = $params["start-day"];

            if (array_key_exists("start-min-time", $params) && $params["start-min-time"] != '') {
                $minD .= ' ' . $params["start-min-time"];
            }
            $sqlQuery->where('dateD', '>=', $minD);

            if (array_key_exists("start-max-time", $params) && $params["start-max-time"] != '') {
                $maxD = $params["start-day"];
                $maxD .= ' ' . $params["start-max-time"];
                $sqlQuery->where('dateD', '<=', $maxD);
//                 $params["maxD"] = $maxD;
            }
        }
        $whereMaxA = [];
        if (array_key_exists("end-day", $params)) {

            if (array_key_exists("end-min-time", $params) && $params["end-min-time"] != '') {
                $minA = $params["end-day"];
                $minA .= ' ' . $params["end-min-time"];
//                 $whereMinA[] = ['dateA', '>=', $minA];
                $sqlQuery->where('dateA', '>=', $minA);
            }

            $maxA = $params["end-day"];
            if (array_key_exists("end-max-time", $params) && $params["end-max-time"] != '') {
                $maxA .= ' ' . $params["end-max-time"];
//                 $params["maxA"] = $maxA;
            }
            $whereMaxA[] = ['dateA', '<=', $maxA];
//             $sqlQuery->where('dateA', '<=', $maxA);
        }

        if (array_key_exists("start-town", $params) && $params["start-town"] != '') {
            $sqlQuery->where('villeD', '=', $params["start-town"]);
        }
        if (array_key_exists("end-town", $params) && $params["end-town"] != '') {
            //$sqlQuery->whereRaw('(villeA = ? or idTrajet in (select InterCity.idTrip from InterCity where city = ?))', [$params["end-town"], $params["end-town"]]);//Autorise l'arrivée à être une étape
            //
//             $sqlQuery = $sqlQuery->where('villeA', '=', $params["end-town"]);

            $sqlQuery->where(function ($q) use ($params, $whereMaxA) {
//                 $inter = InterCity::select('idTrip')->where('city', '=', $params['end-town'])->get()->all();
//                 $inter = array_map(function ($row) { return $row['idTrip']; }, $inter);

                //Ne pas appliquer le min d'arrivé sur les intermédiaires.
                $q->where(function ($q) use ($params, $whereMaxA) {
                    $q->where('villeA', '=', $params['end-town'])->where($whereMaxA);
                });
//                 $q->orWhereIn('idTrajet', $inter);
                $q->orWhereIn('idTrajet', function ($q) use ($params) {
                    $q->select('idTrip')->from("InterCity")->where('city', '=', $params['end-town']);
                });
            });
        } else {
            $sqlQuery->where($whereMaxA);
        }
        if (!array_key_exists("public", $params)) {
            $sqlQuery->where('listePrivee', '!=', NULL);
        }
        if (!array_key_exists("private", $params)) {
            $sqlQuery->where('listePrivee', '=', NULL);
        }
        $sqlQuery->where(function ($q) use ($params, $conducteur) {
            $q->where('listePrivee', '=', NULL);
            $q->orWhereIn('listePrivee', function ($q) use ($params, $conducteur) {
                $q->select('idListe')->from("appartientAListe")->where('idUser', '=', $conducteur);
            });
            $q->orWhereIn('listePrivee', function ($q) use ($params, $conducteur) {
                $q->select('idListe')->from("ListeUtilisateur")->where('createur', '=', $conducteur);
            });
        });
        
        if (!array_key_exists("feminin", $params)) {
            $sqlQuery->where('sexe', '!=', "0");
        }
        if (!array_key_exists("masculin", $params)) {
            $sqlQuery->where('sexe', '!=', "1");
        }
        if (!array_key_exists("prix", $params)) {
            $prix = explode(' - ', str_replace('€', '', $params["price"]));
            $prix[0] = intval($prix[0]);
            $prix[1] = intval($prix[1]);
            $params["prix"] = $prix;
            $sqlQuery = $sqlQuery->where('prix', '>=', $prix[0]);
            $sqlQuery = $sqlQuery->where('prix', '<=', $prix[1]);
        }


        // NOTE Il est possible de passer plusieurs prédicats à la fonction `where`
        //   Ainsi, il est possible de simplement concaténer les prédicats de cette façon :
        //   ```php
        //   $predicates = [];
        //   if (!empty($villeDepart = $params['start-town'] ?? '')) $predicates[] = ['villeD', '=', $villeDepart];
        //   // ... faire de même pour tous les autres paramètres
        //   ```
        //   puis de faire l'appel `$t = Trip::where($predicates)->all();

        $t = $sqlQuery->get()->all();

        $respList = [];
        foreach ($t as $key => $trip) {
            $respItem = [];

            $ic = InterCity::where('idTrip', '=', $trip->idTrajet)->get()->all();
            // une fonction existe déjà pour ça dans le trajet
            if ($ic) {
                $interCity = [];
                foreach ($ic as $inter) {
                    $interCity[] = $inter->city;
                }
                $respItem["interCity"] = $interCity;
            }
            $respItem["arrivéeAproximative"] = $whereMaxA != [] && $trip->dateA > $maxA;

            $respItem["stars"] = User::find($trip->idUser)->moyenne;
            $respItem["prive"] = $trip->listePrivee ? true : false;

            $dateD = explode(' ', $trip->dateD);
            $respItem["dateD"] = $dateD[0];
            $respItem["heureD"] = $dateD[1];
            $dateA = explode(' ', $trip->dateA);
            $respItem["dateA"] = $dateA[0];
            $respItem["heureA"] = $dateA[1];


            $respItem["photo"] = $trip->photo ?: VroumApp::getInstance()->urlFor('img', ["routes" => "icons/defaultPP.jpg"]);
            $respItem["prenom"] = $trip->score;

            $respItem["prenom"] = $trip->prenom;
            $respItem["sexe"] = $trip->sexe;
            $respItem["idTrajet"] = $trip->idTrajet;
            $respItem["conducteur"] = $trip->conducteur;
            $respItem["villeD"] = $trip->villeD;
            $respItem["villeA"] = $trip->villeA;
            $respItem["prix"] = $trip->prix;
            $respList[] = $respItem;

        }

        $resp->getBody()->write(json_encode($respList));
        return $resp->withHeader('Content-Type', 'application/json');
    }

    public static function searchTrip($req, Response $resp, $args)
    {
        RedirectManager::getInstance()->refreshCookieUrlRedirect($req, $args);
        $iv = new TripView();
        $resp->getBody()->write($iv->renderSearchTrip());
        return $resp;
    }

    public static function consultTrip($req, $resp, $args)
    {
        $t = Trip::where('idTrajet', '=', $args['id'])->first();
        $idConnected=ConnectionManager::getInstance()->getIdConnected();
        $userConnected=User::find($idConnected);
        if ($t != NULL && $t->cancelled != 1) {
            RedirectManager::getInstance()->refreshCookieUrlRedirect($req, $args);
            $d = User::where('idUser', '=', $t->conducteur)->first();
            $p = $t->participants()->get();
            $i = $t->intermediateCities()->get();
            $iv = new TripView();

            $notesMoyennes[$d->idUser] = User::find($d->idUser)->moyenne;
            foreach ($p as $carpooler) {
                $notesMoyennes[$carpooler->idUser] = User::find($carpooler->idUser)->moyenne;
            }

            $n = Note::where("note", '=', $d->idUser)->where("notant", '=', $idConnected)->where("idTrajet", '=', $t->idTrajet)->first();
            if ($n == null) {
                $notesDonnees[$d->idUser] = User::NOTATION_DEFAULT;
            } else {
                $notesDonnees[$d->idUser] = $n->notation;
            }
            foreach ($p as $carpooler) {
                $n = Note::where("note", '=', $carpooler->idUser)->first();
                if ($n == null) {
                    $notesDonnees[$carpooler->idUser] = User::NOTATION_DEFAULT;
                } else {
                    $notesDonnees[$carpooler->idUser] = $n->notation;
                }
            }
            $howIsUserInTrip = $userConnected->howIsUserInTrip($t->idTrajet);
            $resp->getBody()->write($iv->renderTrip($t, $d, $p, $notesMoyennes, $notesDonnees, $i, $howIsUserInTrip));
        } else {
//            $resp->getBody()->write('Ce trajet n\'existe pas !');
//            $resp=$resp->withStatus(404);

            //On redirige vers la page de redirection
            $url = RedirectManager::getInstance()->getUrlRedirect();
            return $resp->withHeader('Location', VroumApp::getInstance()->urlFor($url["route"], $url["param"]));
        }
        return $resp;
    }

    public static function cancelTrip_post($req, $resp, $args)
    {
        $params = (array)$req->getParsedBody();
        $idTrip = $params['idTrip'];

        if (self::extern_cancelTrip($idTrip)) {
            //On redirige vers la page de redirection
            $url = RedirectManager::getInstance()->getUrlRedirect();
            $resp->getBody()->write(VroumApp::getInstance()->urlFor($url["route"], $url["param"]));
        } else {
            //Pour l'instant si le cancel ne fonctionne pas on reste sur la page
            $resp->getBody()->write(VroumApp::getInstance()->urlFor("consultTrip", ["id" => $idTrip]));
        }

        return $resp;

    }

    public static function extern_cancelTrip($idTrip) {
        $idUserConnected = ConnectionManager::getInstance()->getIdConnected();
        $t = Trip::where('idTrajet', '=', $idTrip)->first();
        $datePlusOneDay = date('Y-m-d', strtotime("+1 day"));

        if ($t->conducteur == $idUserConnected && $t->dateD >= $datePlusOneDay && $t->cancelled != 1) {
            $t->cancelled = 1;
            $t->save();

            Notification::where('trajet','=',$idTrip)->delete();

            $part=$t->participants()->get()->all();
            foreach ($part as $p){
                $notif = new Notification();
                $notif->destinataire=$p->idUser;
                $notif->expediteur=$t->conducteur;
                $notif->trajet=$idTrip;
                $notif->texte="";
                $notif->type=Notification::TYPE_DELETE_TRIP_OFFER;
                $notif->save();
            }

            return TRUE;
        }

        return FALSE;
    }

    public static function myTrips($req, Response $resp, $args)
    {
        RedirectManager::getInstance()->refreshCookieUrlRedirect($req, $args);
        $iv = new TripView();

        $idConnected = ConnectionManager::getInstance()->getIdConnected();
        $userConnected = User::find($idConnected);
        $now = date('Y-m-d H:i:s');

        /*
         * Methode pas très propre mais je n'ai pas réussi à faire autrement
         * @author Marius
         */

        //On prends tous les futurs trajets ou l'utilisateur a
        $tripsWaitingAnswerParticipation = $userConnected->trips_where_user_waiting_answer_participation()
            ->where('cancelled', '!=', '1')
            ->where('dateD', '>=', $now)
            ->join('User', 'conducteur', 'idUser');

        //On met les trajets ou l'utilisateur est conducteur
        $tripsUser = $userConnected->trips_where_driver()->where('cancelled', '!=', '1')->join('User', 'conducteur', 'idUser');
        $tripsUserAVenir = clone $tripsUser;
        $tripsUserPasses = clone $tripsUser;
        $tripsUserAVenir = $tripsUserAVenir->where('dateD', '>=', $now);
        $tripsUserPasses = $tripsUserPasses->where('dateD', '<', $now);

        //On recupère les trajets ou l'utilisateur est passager
        $tripsUserCarpooler = $userConnected->trips_where_carpooler()->where('cancelled', '!=', '1')->join('User', 'conducteur', 'idUser');
        $tripsUserCarpoolerAVenir = clone $tripsUserCarpooler;
        $tripsUserCarpoolerPasses = clone $tripsUserCarpooler;
        $tripsUserCarpoolerAVenir = $tripsUserCarpoolerAVenir->where('dateD', '>=', $now);
        $tripsUserCarpoolerPasses = $tripsUserCarpoolerPasses->where('dateD', '<', $now);

        //On fait une union de tous les trajets recupérés
        $tripsUserAVenir->union($tripsUserCarpoolerAVenir);
        $tripsUserPasses->union($tripsUserCarpoolerPasses);

        $trajetsWaitingAnswerParticipation = $tripsWaitingAnswerParticipation->get();
        $trajetsAVenir = $tripsUserAVenir->get();
        $trajetsPasses = $tripsUserPasses->get();


        $trajetsWaitingAnswerParticipation->map(function ($t_u) {
            $t_u->note = User::find($t_u->idUser)->moyenne;
        });
        $trajetsAVenir->map(function ($t_u) {
            $t_u->note = User::find($t_u->idUser)->moyenne;
        });
        $trajetsPasses->map(function ($t_u) {
            $t_u->note = User::find($t_u->idUser)->moyenne;
        });

        $resp = new Response();
        $resp->getBody()->write($iv->renderMyTrips($trajetsWaitingAnswerParticipation,$trajetsAVenir, $trajetsPasses, $userConnected));
        return $resp;
    }

    public static function participateToTrip_post($req, Response $resp, $args)
    {
        $params = (array)$req->getParsedBody();
        $idTrip = $params['idTrip'];
        $idParticipant = $params['idParticipant'];
        $accept = $params['accept'];

        try{
            $idUserConnected = ConnectionManager::getInstance()->getIdConnected();
            $t = Trip::find($idTrip);
            $participeTrajet = ParticipeATrajet::where('participant','=',$idUserConnected)->where('idTrajet','=',$idTrip)->first();

            $notifDemande = Notification::where('trajet','=',$idTrip)
                ->where('destinataire','=',$t->conducteur)
                ->where('expediteur','=',$idParticipant)
                ->where('type','=',Notification::TYPE_ASK_PARTICIPATION_TRIP);

            if ($t != null && $participeTrajet == null && $notifDemande->first() != null && $t->conducteur != $idParticipant && $t->conducteur == $idUserConnected && $t->cancelled != 1) {

                //On supprime la notification de demande
                $notifDemande->delete();

                $notif= new Notification();
                $notif->destinataire=$idParticipant;
                $notif->expediteur=$t->conducteur;
                $notif->trajet=$idTrip;
                $notif->texte="";

                if($accept==1){
                    $pAT = new ParticipeATrajet();
                    $pAT->participant = $idParticipant;
                    $pAT->idTrajet = $idTrip;

                    if(!$pAT->save()){
                        throw new \DomainException("Erreur lors de l'affectation de l'utilisateur #$idParticipant au trajet #$idTrip");
                    }


                    $notif->type=Notification::TYPE_ACCEPT_PARTICIPATION_TRIP;
                } else {
                    $notif->type=Notification::TYPE_REFUSE_PARTICIPATION_TRIP;
                }
                //On envoie la notification
                if(!$notif->save()){
                    throw new \DomainException("Erreur lors de l'envoi de la notification à l'utilisateur #$idParticipant 
                    à propos de sa demande de participation au trajet #$idTrip");
                }
            }
        } catch (\DomainException $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        //On redirige vers la page de redirection
        $url = RedirectManager::getInstance()->getUrlRedirect();
        $resp->getBody()->write(VroumApp::getInstance()->urlFor($url["route"], $url["param"]));
        return $resp;
    }

    public static function askParticipation_post($req, Response $resp, $args)
    {
        $params = (array)$req->getParsedBody();
        $idTrip = $params['id_trip'];
        $msg = $params['msg'];
        $idUserConnected = ConnectionManager::getInstance()->getIdConnected();

        $t = Trip::find($idTrip);
        $participeTrajet = ParticipeATrajet::where('participant','=',$idUserConnected)->where('idTrajet','=',$idTrip)->first();

        if ($t != null && $participeTrajet == null && $t->conducteur != $idUserConnected && $t->cancelled != 1) {
            $notifAnnulDemandePrec = Notification::where('trajet','=',$idTrip)
                ->where('destinataire','=',$t->conducteur)
                ->where('expediteur','=',$idUserConnected)
                ->where('type','=',Notification::TYPE_DELETE_PARTICIPATION_TRIP)->delete();

            $notif= new Notification();
            $notif->destinataire=$t->conducteur;
            $notif->expediteur=$idUserConnected;
            $notif->trajet=$idTrip;
            $notif->texte=$msg;
            $notif->type=Notification::TYPE_ASK_PARTICIPATION_TRIP;
            $notif->save();
        }

        //On redirige vers la page de redirection
        $url = RedirectManager::getInstance()->getUrlRedirect();
        $resp->getBody()->write(VroumApp::getInstance()->urlFor($url["route"], $url["param"]));
        return $resp;
    }

    public static function cancelParticipationTrip_post($req, Response $resp, $args)
    {
        $params = (array)$req->getParsedBody();
        $idTrip = $params['id_trip'];
        $msg = isset($params['msg'])?$params['msg']:"";

        $idUserConnected = ConnectionManager::getInstance()->getIdConnected();
        $userConnected = User::find($idUserConnected);

        $t = Trip::find($idTrip);

        $howIsUserInTrip = $userConnected->howIsUserInTrip($t->idTrajet);
        $done = false;

        if($howIsUserInTrip==User::CARPOOLER_TRIP){
            $participeTrajet = ParticipeATrajet::where('participant','=',$idUserConnected)->where('idTrajet','=',$idTrip);
            if($participeTrajet->first() != null){
                $participeTrajet->delete();
                $done = true;
            }
        } elseif ($howIsUserInTrip==User::WAITING_ANSWER_PARTICIPATION_TRIP){
            $notifDemande = Notification::where('trajet','=',$idTrip)
                ->where('destinataire','=',$t->conducteur)
                ->where('expediteur','=',$idUserConnected)
                ->where('type','=',Notification::TYPE_ASK_PARTICIPATION_TRIP);
            if($notifDemande->first() != null){
                $notifDemande->delete();
                $done=true;
            }
        }
        if($done){
            //Si l'annulation a eu lieu on peut envoyer un mail au conducteur pour lui notifier l'annulation de la demande de participation ou de la participation (même notification)
            $notif= new Notification();
            $notif->destinataire=$t->conducteur;
            $notif->expediteur=$idUserConnected;
            $notif->trajet=$idTrip;
            $notif->texte=$msg;
            $notif->type=Notification::TYPE_DELETE_PARTICIPATION_TRIP;
            $notif->save();
        }

        //On redirige vers la page de redirection
        $url = RedirectManager::getInstance()->getUrlRedirect();
        $resp->getBody()->write(VroumApp::getInstance()->urlFor($url["route"], $url["param"]));
        return $resp;
    }

}

?>
