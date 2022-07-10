<?php

namespace Vroum\Controler;

use Vroum\Middleware\RedirectNotConnectedMiddleware;
use Vroum\Model\Notification;
use Vroum\Utils\Arrays;
use Vroum\Model\User;
use Vroum\View\NotificationView;
use Vroum\VroumApp;

class NotificationController {
    public static function notificationsPerUser($req, $resp, $args) {
        RedirectManager::getInstance()->refreshCookieUrlRedirect($req,$args);
        $ns = Notification::where('destinataire', '=', ConnectionManager::getInstance()->getIdConnected())
            ->get()->all();
        $notifications = [];

        foreach ($ns as $n) {
            $notifications[$n['expediteur']][] = $n;
        }

        $notifications = Arrays::map_with_key(function ($userId, $notifs) {
            $u = User::select('nom', 'prenom')->where('idUser', '=', $userId)->first();
            $nbNonLues = count(array_filter($notifs, function ($notif) { return !((bool) $notif['lue']); }));

            return [$userId, ['nom' => $u['nom'], 'prenom' => $u['prenom'], 'nbNonLues' => $nbNonLues]];
        }, $notifications);

        $nv = new NotificationView();
        $resp->getBody()->write($nv->renderNotificationSender($notifications));

        return $resp;
    }

    public static function allNotificationsFromUser($req, $resp, $args) {
        $idConnected =ConnectionManager::getInstance()->getIdConnected();
        $user = User::find($args['id']);
        $notifs = Notification::where('destinataire', '=', $idConnected)
            ->where('expediteur', '=', $user->idUser);

        if(count($notifs->get()->all())==0){
            //S'il n'y pas de notifications pour cet utilisateur on retourne sur la page des notifications par user
            return $resp->withHeader('Location', VroumApp::getInstance()->urlFor("notificationsPerUser"));
        }
        $notifs_to_modify = clone $notifs;

        $notifs = $notifs->get()->all();

        $notifs_to_modify->update(['lue' => 1]);

        $nv = new NotificationView();
        $resp->getBody()->write($nv->renderAllNotificationsFromUser($notifs,$user));

        return $resp;
    }

    public static function deleteNotification_post($req, $resp, $args) {
        $idConnected =ConnectionManager::getInstance()->getIdConnected();
        $params = (array)$req->getParsedBody();
        $idNotif = $params['id'];
        $nq = Notification::where('idNotif', '=', $idNotif);
        $notif = $nq->first();
        $exp = $notif->expediteur;
        if($notif->deletable) {
            if ($nq->delete()) {
                $resp->getBody()->write(VroumApp::getInstance()->urlFor("allNotificationsFromUser", ['id' => $exp]));
                return $resp;
            }
        }

        $resp->getBody()->write(json_encode(['err' => "Impossible de supprimer la notification #$idNotif (peut-être qu'elle n'existe pas)."]));
        return $resp->withStatus(400);
    }

    public static function deleteNotificationFromUser_post($req, $resp, $args) {
        $idConnected = ConnectionManager::getInstance()->getIdConnected();
        $params = (array)$req->getParsedBody();
        $idUser = $params['id'];
        $notifs = Notification::where('destinataire', '=', $idConnected)->where('expediteur', '=', $idUser)->get()->all();
        if(count($notifs)>0) {
            foreach ($notifs as $n) {
                if ($n->deletable) {
                    if (!$n->delete()) {
                        $resp->getBody()->write(json_encode(['err' => "Impossible de supprimer la notification #$n->idNotif provenant de l'user #$idUser."]));
                        return $resp->withStatus(400);
                    }

                }
            }
            $resp->getBody()->write(VroumApp::getInstance()->urlFor("allNotificationsFromUser", ['id' => $notifs[0]->expediteur]));
        } else {
            $resp->getBody()->write(VroumApp::getInstance()->urlFor("notificationsPerUser"));
        }
        return $resp;
    }
}

?>
