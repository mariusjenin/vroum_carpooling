<?php

namespace Vroum\Controler;

use Slim\Psr7\Response;
use Vroum\Model\Note;
use Vroum\Model\ParticipeATrajet;
use Vroum\Model\Trip;

class RatingController
{


    public function rateCarpooler_post($req, $resp, $args)
    {
        $resp = new Response();
        $params = (array)$req->getParsedBody();
        //On récupère les paramètre du post
        $idTrip = $params['trip_id'];
        $numStar = $params['num_star'];
        $idUser = $params['id_user'];
        //On recupère l'id de l'user connecté
        $idConnectedUser = ConnectionManager::getInstance()->getIdConnected();
        //Date actuelle
        $date = date('d/m/y H:i');

        $t = Trip::where('idTrajet', '=', $idTrip)->first();
        $pATNote = ParticipeATrajet::where("idTrajet", '=', $idTrip)->where("participant", '=', $idUser)->first();
        $pATNotant = ParticipeATrajet::where("idTrajet", '=', $idTrip)->where("participant", '=', $idConnectedUser)->first();

        //Noté est soit conducteur soit un des covoitureurs
        $noteValid = $pATNote != null || $t->conducteur == $idUser;

        //Notant est soit conducteur soit un des covoitureurs
        $notantValid = $pATNotant != null || $t->conducteur == $idConnectedUser;

        $datePlusOneDay = date('Y-m-d', strtotime('+1 day'));
        //Vérification
        if ($idConnectedUser != $idUser &&
            strtotime($t->dateA) <= strtotime($datePlusOneDay) &&
            $noteValid && $notantValid
        ) {
            //On recupère la note si elle existe deja pour la modifier sinon on en créé une nouvelle
            $n = Note::where('note', '=', $idUser)->where('notant', '=', $idConnectedUser)->where('idTrajet', '=', $idTrip)->first();
            if ($n == null) {
                $n = new Note();
                $n->idTrajet = $idTrip;
                $n->note = $idUser;
                $n->notant = $idConnectedUser;
            }
            $n->notation = $numStar;
            if (!$n->save()) {
                $resp->getBody()->write('Erreur lors de la sauvegarde de la note du covoitureur');
                $resp = $resp->withStatus(500);
            }
        } else {
            $resp->getBody()->write("Vous n'avez pas les permissions pour noter cet utilisateur sur ce trajet");
            $resp = $resp->withStatus(403);
        }

        return $resp;
    }

}

?>
