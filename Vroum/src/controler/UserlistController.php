<?php

namespace Vroum\Controler;

use Vroum\Model\ListeUtilisateur;
use Vroum\Model\AppartientAListe;
use Vroum\Model\User;
use Vroum\View\UserlistView;
use Vroum\Utils\Arrays;
use Vroum\VroumApp;

class UserlistController {
    public static function createOrEditList_post($req, $resp, $args) {
        $params = (array) $req->getParsedBody();

        $name = $params['name_list'] ?? '';
        $users = $params['users_list'] ?? [];
        $idListeToModify = $params['id_liste'] ?? '';

        try {
            if (empty($name = htmlspecialchars($name)))
                throw new \DomainException(json_encode([ 'err' => 'Veuillez spécifier le nom de votre nouvelle liste d\'utilisateurs', 'field' => 'nom' ]));

            $myId = ConnectionManager::getInstance()->getIdConnected();
            $u = User::find($myId);
            $myEmail = $u['email'];

            $users = Arrays::filter_map($users, function ($userEmail) use ($myEmail) {
                return $userEmail !== $myEmail && User::where('email', '=', $userEmail)->first();
            }, function ($userEmail) {
                return User::where('email', '=', $userEmail)->first();
            });

            $l = NULL;
            if (!empty($idListeToModify)) {
                if (ListeUtilisateur::where('nom', '=', $name)->where('createur', '=', $myId)->where('idListe', '<>', $idListeToModify)->first())
                    throw new \DomainException(json_encode(['err' => "Une de vos listes s'appelle déjà '$name'"]));

                $l = ListeUtilisateur::find($idListeToModify);
                $l->nom = $name;
                $l->save();
            } else {
                $l = ListeUtilisateur::updateOrCreate(
                    ['nom' => $name, 'createur' => $myId],
                    ['nom' => $name]
                );
            }

            // `$users`  contient toute la liste
            $oldUsers = AppartientAListe::where('idListe', '=', $l->idListe)->get()->all();
            // $oldUsers - $users = les utilisateurs qui étaient là et qui ne le sont plus
            $usersToRemove = array_udiff($oldUsers, $users, function ($u1, $u2) {
                return $u1['idUser'] <=> $u2['idUser'];
            });
            AppartientAListe::whereIn('idUser', array_map(function ($u) { return $u['idUser']; }, $usersToRemove))->delete();
            // $users - $oldUsers = les utilisateurs qui ne sont pas encore là
            $usersToAdd = array_udiff($users, $oldUsers, function ($u1, $u2) {
                return $u1['idUser'] <=> $u2['idUser'];
            });

            foreach ($usersToAdd as $user) {
                AppartientAListe::insert(['idUser' => $user['idUser'], 'idListe' => $l->idListe]);
            }
        } catch (\DomainException $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $resp->getBody()->write(VroumApp::getInstance()->urlFor("myUserLists"));
        return $resp;
    }

    public static function myLists($req, $resp, $args) {
        RedirectManager::getInstance()->refreshCookieUrlRedirect($req,$args);
        $lists = ListeUtilisateur::where('createur', '=', ConnectionManager::getInstance()->getIdConnected())
               ->get()->map(function ($l) { return [$l['idListe'], [$l['nom'], $l->inscrits()->get()->all()]]; });
        $lists = Arrays::map_with_key(function ($_, $l) { return $l; }, $lists->all());

        // $lists est une `array<string, Vroum\Model\User>` qui associe les noms des listes avec les utilisateurs inscrits
        $ulv = new UserlistView();
        $resp->getBody()->write($ulv->renderUserlist($lists));

        return $resp;
    }

    public static function deleteList_post($req, $resp, $args) {
        $params = (array) $req->getParsedBody();

        $id = $params['id'];

        try {
            $list = ListeUtilisateur::where('idListe', '=', $id)
                  ->where('createur', '=', ConnectionManager::getInstance()->getIdConnected())
                  ->first();
            if (!$list)
                throw new \DomainException(json_encode(['err' => "Liste #$id inconnue", 'field' => 'idListe']));

            $trips = $list->trajetsConcernes;

            foreach ($trips as $trip) {
                TripController::extern_cancelTrip($trip['idTrajet']);

                $trip->listePrivee = NULL;
                $trip->save();
            }

            AppartientAListe::where('idListe', '=', $id)->delete();
            $list->delete();
        } catch (\DomainException $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $resp->getBody()->write(VroumApp::getInstance()->urlFor("myUserLists"));
        return $resp;
    }

    public static function createUserList($req, $resp, $args){
        $iv = new UserlistView();
        $resp->getBody()->write($iv->renderCreateOrEditList(NULL));
        return $resp;
    }

    public static function editUserList($req, $resp, $args){
        $id = $args['id'];
        $list = AppartientAListe::where('idListe', '=', $id)
            ->join('User', 'User.idUser', '=', 'appartientAListe.idUser')
            ->get()
            ->all();
        $res['nom'] = ListeUtilisateur::find($id)->nom;
        $res['id'] = $id;
        $res['email'] = [];
        foreach($list as $u){
                $res['email'][] = $u->email;
        }
        $iv = new UserlistView();
        $resp->getBody()->write($iv->renderCreateOrEditList($res));
        return $resp;
    }
}

?>
