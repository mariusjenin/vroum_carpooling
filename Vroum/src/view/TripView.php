<?php


namespace Vroum\View;


use Vroum\Controler\ConnectionManager;
use Vroum\Controler\GlobalController;
use Vroum\Model\User;
use Vroum\Model\ListeUtilisateur;
use Vroum\VroumApp;

class TripView
{

    public function __construct()
    {
    }

    /**
     * Renvoie l'html de la page Proposer un trajet
     * @return string
     */
    public function renderCreateTrip()
    {
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();
        $datePlusOneDay = date('Y-m-d', strtotime('+1 day'));
        $hourPlusOneDay = date('H:i', strtotime('+1 day'));
        $datePlusOneDayOneHour = date('Y-m-d', strtotime('+1 day +1 hour'));
        $hourPlusOneDayOneHour = date('H:i', strtotime('+1 day +1 hour'));

        $listeListe = "";
        $listes = ListeUtilisateur::where('createur', '=', ConnectionManager::getInstance()->getIdConnected())->get()->all();
        foreach ($listes as $l) {
             $listeListe .= "<option value=\"{$l->idListe}\">{$l->nom}</option>";
        }

        $gc = new GlobalController();
        $res = $gc->head("Proposer un trajet", [
            $routeParser->urlFor('css', ["routes" => "create_trip.css"]),
            $routeParser->urlFor('css', ["routes" => "trip.css"])]);
        $res .= $gc->navbar();
        $res .= <<<END
<div class="container-fluid mb-3 first_element_page_with_menu h-100">
    <div class="row justify-content-center">
        <div class="d-flex justify-content-center col">
            <div class="notch_title_page_fixed p-1 px-5 m-0">Proposer un trajet</div>
        </div>
    </div>
    <form class="pt-5" name="form_create_trip" action="{$routeParser->urlFor('newTrip')}"  data-method="post" onsubmit="return submit_form(event);">

        <div class="row mt-4">
            <div class="error_form d-none w-100 text-danger font-weight-bold justify-content-center align-items-center pl-3 pr-3 mb-2">
                Certaines valeurs ne conviennent pas
            </div>
            <div class="col-12 col-lg-4 mt-3 order-0">
                <div class="h-100 d-flex flex-column justify-content-around align-items-center create_trip_input p-3">
                    <p class="font-weight-bold">Départ *</p>
                    <input type="text" class="form-control mb-3" required
                           name="start-town" placeholder="Ville de départ"/>
                    <input type="date" class="form-control mb-3" value="{$datePlusOneDay}" required
                           name="start-day">
                    <input type="time" class="form-control" value="{$hourPlusOneDay}" required
                           name="start-time" step="60">
                </div>
            </div>
            <div class="col-12 col-lg-4 mt-3 order-2 order-lg-1">
                <div class="h-100 create_trip_input p-3">
                    <p class="font-weight-bold">Villes intermédiaires</p>
                    <div class="d-flex flex-row align-items-center justify-content-end w-100 mb-3">
                        <input type="text" class="intermediate_city_input d-inline form-control"
                               placeholder="Ville intermédiaire (facultatif)">
                        <div onclick="add_city_to_list($(this).prev()[0].value,$(this).prev()[0],$($(this).parent().parent().children()[2]));" class="btn_add_intermediate_city">
                            <img class="mw-100 mh-100" src="{$routeParser->urlFor('img', ["routes" => "icons/add_fill.png"])}"/>
                        </div>
                    </div>
                    <div class="w-100 d-flex flex-row flex-wrap justify-content-center align-items-center">
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4 mt-3 order-1 order-lg-2">
                <div class="h-100 d-flex flex-column justify-content-around align-items-center create_trip_input p-3">
                    <p class="font-weight-bold">Arrivée *</p>
                    <input type="text" class="form-control mb-3" required
                           name="end-town" placeholder="Ville d'arrivée"/>
                    <input type="date" class="form-control mb-3" value="{$datePlusOneDayOneHour}" required
                           name="end-day">
                    <input type="time" class="form-control" value="{$hourPlusOneDayOneHour}" required
                           name="end-time" step="60">
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12 mt-3">
                <div class="h-100 create_trip_input p-3">
                    <p class="font-weight-bold ml-1 h5">Trajet</p>
                    <div class="row">
                        <div class="col-12 col-md-4 mt-3">
                            <input id="private" type="checkbox" class="form-check-inline ml-2" name="private"
                                   value="false">
                            <label for="private" class="font-weight-bold mr-1">Privé</label>
                            <select class="form-control hide_with_check_false" name="users-user-list" id="users-user-list-list">
                            {$listeListe}
                            </select>
                        </div>
                        <div class="col-12 col-md-4 mt-3">
                            <label for="max_seats" class="disabled font-weight-bold">Places libres *</label>
                            <input id="max_seats" type="number" class="form-control" name="max_seats"
                                   value="1" min="1" required>
                        </div>
                        <div class="col-12 col-md-4 mt-3">
                            <label for="price" class="disabled font-weight-bold">Prix *</label>
                            <input id="price" type="number" class="form-control" name="price" placeholder="Votre prix"
                                   min="0" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12 mt-3">
                <div class="h-100 create_trip_input p-3">
                    <div class="row">
                        <div class="col-12">
                          <textarea type="text" size=200 class="form-control" name="constraints"
                                    placeholder="Précisions sur le lieux de départ (facultatif)"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12 mt-3">
                <div class="h-100 create_trip_input p-3">
                    <div class="row">
                        <div class="col-12 col-md-6">
                          <textarea type="text" size=200 class="form-control" name="precisions"
                                    placeholder="Commentaire (facultatif)"></textarea>
                        </div>
                        <div class="col-12 col-md-6 d-flex justify-content-center">
                            <input class="mt-2 mt-md-0 pt-4 pb-4 pt-md-0 pb-md-0 w-100 creation_trajet_submit font-weight-bold text-white btn button"
                                   type="submit" value="Créer le trajet">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
END;
        $res .= $gc->foot([
            $routeParser->urlFor('js', ["routes" => "create_trip.js"]),
            $routeParser->urlFor('js', ["routes" => "submit_form.js"]),
            $routeParser->urlFor('js', ["routes" => "form_trip.js"])
        ]);
        return $res;
    }

    /**
     * Renvoie l'html de la page Rechercher un trajet
     * @return string
     */
    public function renderSearchTrip()
    {
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();

        $datePlusOneDay = date('Y-m-d', strtotime("+1 day"));
        $startHour = '00:00';
        $endHour = '23:59';

        $gc = new GlobalController();
        $res = $gc->head("Rechercher un trajet", [$routeParser->urlFor('css', ["routes" => "search_trip.css"]), "//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"]);
        $res .= $gc->navbar();
        $res .= <<<END
<div class="container-fluid first_element_page_with_menu h-100">
    <div class="d-flex justify-content-center">
        <div class="notch_title_page_fixed p-2 px-5 m-0">Rechercher un trajet
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <a href="{$routeParser->urlFor('newTrip')}" class="notch_right_page_absolute p-2 pl-5 pr-3 m-0 d-none d-md-block">Proposer un trajet

            <div class="position-absolute btn_hoverable add_trip d-flex justify-content-center align-items-center">
                <img class="mw-100 mh-100" src="{$routeParser->urlFor('img', ["routes" => "icons/add_fill.png"])}"/>
            </div>
        </a>
    </div>

    <form name="" class="pt-5" action="{$routeParser->urlFor('searchTrip_post')}" data-method="post" onsubmit="loading_search_trip(true);no_result(false); return submit_form(event,done_search_trip);">
        <div class="row mt-4 mb-4">
            <div class="col-12 col-lg-6 mt-3 order-0">
                <div class="h-100 d-flex flex-column justify-content-around align-items-center search_trip_box p-3">
                    <label for="start-town" class="font-weight-bold">Départ</label>
                    <input type="text" id="start-town" class="form-control mb-3"
                           name="start-town" placeholder="Ville de départ" required>
                    <input type="date" class="form-control mb-3"
                           name="start-day" value="{$datePlusOneDay}" required>
                    <div class="row w-100">
                        <div class="col-6 pl-0 d-flex flex-column align-items-center">
                            <label class="font-weight-bold">Entre</label>
                            <input type="time" class="form-control"
                                   name="start-min-time" step="60" value="{$startHour}" required>
                        </div>
                        <div class="col-6 pr-0 d-flex flex-column align-items-center">
                            <label class="font-weight-bold">Et</label>
                            <input type="time" class="form-control"
                                   name="start-max-time" step="60" value="{$endHour}" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6 mt-3 order-0">
                <div class="h-100 d-flex flex-column justify-content-around align-items-center search_trip_box p-3">
                    <label for="end-town" class="font-weight-bold">Arrivée</label>
                    <input type="text" id="end-town" class="form-control mb-3"
                           name="end-town" placeholder="Ville d'arrivée" required>
                    <input type="date" class="form-control mb-3"
                           name="end-day" value="{$datePlusOneDay}" required>
                    <div class="row w-100">
                        <div class="col-6 pl-0 d-flex flex-column align-items-center">
                            <label class="font-weight-bold">Entre</label>

                            <input type="time" class="form-control"
                                   name="end-min-time" step="60" value="{$startHour}" required>
                        </div>
                        <div class="col-6 pr-0 d-flex flex-column align-items-center">
                            <label class="font-weight-bold">Et</label>
                            <input type="time" class="form-control"
                                   name="end-max-time" step="60" value="{$endHour}" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-100">
            <div class="row">
                <div class="col-12 col-lg-8 mb-3 mb-lg-0">
                    <div class="h-100 d-flex flex-column justify-content-around search_trip_box p-3">
                        <p class="font-weight-bold align-self-center">Recherche avancée</p>
                        <div>
                            <div class="font-weight-bold">Trajet</div>

                            <div class="d-inline-block mr-3">
                                <input type="checkbox" id="public" name="public" checked>
                                <label for="public">Public</label>
                            </div>
                            <div class="d-inline-block">
                                <input type="checkbox" id="private" name="private" checked>
                                <label for="private">Privé</label>
                            </div>
                            <img class="lock_picture_mini" src="{$routeParser->urlFor('img', ["routes" => "icons/lock.png"])}">
                        </div>
                        <div>
                            <div class="font-weight-bold">Sexe</div>


                            <div class="d-inline-block mr-3">
                                <input type="checkbox" id="masculin" name="masculin" checked>
                                <label for="masculin">Masculin</label>
                            </div>
                            <div class="d-inline-block">
                                <input type="checkbox" id="feminin" name="feminin" checked>
                                <label for="feminin">Féminin</label>
                            </div>
                        </div>
                        <div>
                            <label class="font-weight-bold">Prix : </label>
                            <input type="text" id="amount" name="price" class="amount_input_search_trip" readonly>
                            <div id="slider-range"></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="d-flex justify-content-center align-items-center h-100">
                        <input class="submit_search_trip btn_hoverable py-3 px-5 font-weight-bold text-white btn button"
                               type="submit" value="Rechercher">
                    </div>
                </div>
            </div>
        </div>
    </form>
    <hr class="hr_search_trip">
    <div class="font-weight-bold d-none title_result text-center">Résultat</div>
    <hr class="d-none hr_search_trip hr_hidden_search_trip">

    <div class="font-italic d-none text-center no_result_search_trip">
        Pas de resultats pour votre recherche
    </div>

    <div class="d-none loading_search_trip flex-row justify-content-center my-2">
        <img src="../img/loading/loading.gif">
    </div>
    <div data-url_consult="{$routeParser->urlFor('consultTrip', ["id" => ""])}" class="trip_list">

    </div>

    <hr class="d-none hr_search_trip hr_hidden_search_trip">
</div>
END;

        $res .= $gc->foot(["https://code.jquery.com/ui/1.12.1/jquery-ui.js",
            $routeParser->urlFor('js', ["routes" => "submit_form.js"]),
            $routeParser->urlFor('js', ["routes" => "search_trip.js"]),
            $routeParser->urlFor('js', ["routes" => "form_trip.js"])
        ]);
        return $res;
    }


    /**
     * Renvoie l'html de la page Consulter un trajet
     * @param $t
     * @param $d
     * @param $p
     * @param $notesMoyennes
     * @param $notesDonnees
     * @param $i
     * @return string
     */
    public function renderTrip($t, $d, $p, $notesMoyennes, $notesDonnees, $i ,$howIsUserInTrip)
    {
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();
        $gc = new GlobalController();

        $userConnected = ConnectionManager::getInstance()->getIdConnected();
        $userIsDriver = $howIsUserInTrip == User::CONDUCTEUR_TRIP;
        $userIsCarpooler = $howIsUserInTrip == User::CARPOOLER_TRIP;
        $userIsWaitingAnswerParticiaption = $howIsUserInTrip == User::WAITING_ANSWER_PARTICIPATION_TRIP;
        $userIsNothingInTrip = $howIsUserInTrip == User::NOTHING_TRIP;

        $driverSex = $d->sexe == 0 ? 'Femme' : 'Homme';
        $driverPhoto = $d->photo ?: $routeParser->urlFor("img", ["routes" => "icons/defaultPP.jpg"]);

        $arrayD = explode(" ", $t->dateD);
        $dateD = explode("-", $arrayD[0]);
        $dateD = $dateD[2] . "/" . $dateD[1] . "/" . $dateD[0];
        $hourD = substr($arrayD[1], 0, strlen($arrayD[1]) - 3);

        $arrayA = explode(" ", $t->dateA);
        $dateA = explode("-", $arrayA[0]);
        $dateA = $dateA[2] . "/" . $dateA[1] . "/" . $dateA[0];
        $hourA = substr($arrayA[1], 0, strlen($arrayA[1]) - 3);

        $dateT = $dateA . " " . $hourA.":00";
        $dateT = str_replace("/", "-", $dateT);
        $date = date('d/m/Y H:i');

        $nbCarpooler = count($p);
        $nbPlaceRestantes = $t->placeMax - $nbCarpooler;

        $res = $gc->head("Trajet de {$t->villeD} à {$t->villeA}", [
            $routeParser->urlFor("css", ["routes" => "consult_trip.css"]),
            $routeParser->urlFor("css", ["routes" => "modal.css"]),
            $routeParser->urlFor("css", ["routes" => "trip.css"])
        ]);
        $res .= $gc->navbar();
        $res .= <<<END
        <div class="container-fluid mb-3 first_element_page_with_menu h-100">
            <div class="d-flex justify-content-center">
                <div class="notch_title_page_fixed py-1 px-5 m-0">Trajet de {$t->villeD} à {$t->villeA}</div>
            </div>
END;
        if ($userIsDriver) {
            $res .= <<<END
            <div class="d-flex justify-content-end">
                <div data-modal_accept="{$routeParser->urlFor("cancelTrip_post")}" data-modal_accept_data='{ "idTrip" : $t->idTrajet}' class="modal_cancel_trip_consult_trip notch_right_page_absolute py-2 btn_grey_hover_black px-4 m-0 d-none d-md-inline-block"><div class="d-inline">Annuler le trajet</div></div>
            </div>
END;
        }
        $res .= <<<END
            <div class="pt-5">
                <div class="row mt-4">

                    <div class="col-6 col-lg mt-3 order-0">
                        <div class="h-100 d-flex flex-column justify-content-around align-items-center shadowed_box_border_radius p-3">
                            <div class="font-weight-bold h4">
                                Départ
                            </div>
                            <div class="h-100 w-100 d-flex flex-wrap flex-row justify-content-around align-items-center mt-2">
                                <div class="font-weight-bold p-2">
                                    {$t->villeD}
                                </div>
                                <div class="p-2">
                                    le
                                    <div class="d-inline font-weight-bold">{$dateD}</div>
                                </div>
                                <div class="p-2">à environ
                                    <div class="d-inline font-weight-bold">
                                        {$hourD}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
END;

        //On affiche toutes les villes intermédiaires si il y en a
        if (count($i) > 0) {
            $interCitiesHtml = "";
            foreach ($i as $interCity) {
                $interCitiesHtml .= <<<END
            <div class="intermediate_city px-3 py-2 m-1">
                {$interCity->city}
            </div>
END;
            }
            $res .= <<<END
                   <div class="col-12 col-lg mt-3 order-2 order-lg-1">
                       <div class="h-100 justify-content-around d-flex flex-column align-items-center shadowed_box_border_radius p-3">
                           <div class="font-weight-bold h4">
                               Villes intermédiaires
                           </div>
                           <div class="h-100 w-100 d-flex flex-wrap flex-row justify-content-around align-items-center mt-2">
                              {$interCitiesHtml}
                           </div>
                       </div>
                   </div>
END;
        }
        $res .= <<<END
                    <div class="col-6 col-lg mt-3 order-1 order-lg-2">
                        <div class="h-100 d-flex flex-column justify-content-around align-items-center shadowed_box_border_radius p-3">
                            <div class="font-weight-bold h4">
                                Arrivée
                            </div>
                            <div class="h-100 w-100 d-flex flex-wrap flex-row justify-content-around align-items-center mt-2">
                                <div class="font-weight-bold p-2">
                                    {$t->villeA}
                                </div>
                                <div class="p-2">
                                    le
                                    <div class="d-inline font-weight-bold">{$dateA}</div>
                                </div>
                                <div class="p-2">à environ
                                    <div class="d-inline font-weight-bold">
                                        {$hourA}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

END;
        $htmlPlacesRestantes=<<<END
                <div class="d-inline font-weight-bold font-size-1_2rem">
                     {$nbPlaceRestantes}
                </div>
END;
        if($nbPlaceRestantes>1){
            $htmlPlacesRestantes.=<<<END
                place restantes sur
END;
        }else {
            $htmlPlacesRestantes.=<<<END
                place restante sur
END;
        }
        $htmlPlacesMax=<<<END
                <div class="d-inline font-weight-bold font-size-1_2rem">
                    {$t->placeMax}
                </div>
END;
        if($t->placeMax>1){
            $htmlPlacesMax=<<<END
                les
                {$htmlPlacesMax}
                proposées
END;
        }else {
            $htmlPlacesMax=<<<END
            {$htmlPlacesMax}
            proposée
END;
        }
        $htmlPrive = $t->listePrivee ? 'Privé' : 'Public';
        $res.=<<<END
                <div class="row mt-3">
                    <div class="col-12 offset-lg-3 col-lg-6 mt-3">
                        <div class="h-100 shadowed_box_border_radius d-flex flex-column justify-content-around align-items-center p-3">
                            <div class="font-weight-bold ml-1 h4">Trajet</div>
                            <div class="w-100 d-flex flex-row justify-content-around align-items-center">

                                <div class="d-inline font-weight-bold m-1">
                                    {$htmlPrive}
                                </div>
                                <div class="m-1">
                                    {$htmlPlacesRestantes}
                                    {$htmlPlacesMax}
                                </div>
                                <div class="d-inline font-weight-bold font-size-1_5rem m-1">
                                    {$t->prix}€
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
END;

        //On affiche les précisions s'il y en a
        if (!empty($t->precisionsContraintes)) {
            $res .= <<<END
                <div class="row mt-3">
                    <div class="col-12 mt-3">
                        <div class="h-100 shadowed_box_border_radius p-4">
                            <div class="row">
                                <div class="col-12">
                                    <div class="font-weight-bold h5">Précisions sur le lieux de départ :</div>
                                    <div>
                                        {$t->precisionsContraintes}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
END;
        }

        //On affiche les commentaires s'il y en a
        if (!empty($t->precisionsRDV)) {
            $res .= <<<END
                <div class="row mt-3">
                    <div class="col-12 mt-3">
                        <div class="h-100 shadowed_box_border_radius p-4">
                            <div class="row">
                                <div class="col-12">
                                    <div class="font-weight-bold h5">Commentaire du conducteur :</div>
                                    <div>
                                    {$t->precisionsRDV}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
END;
        }
        $res .= <<<END
                <div class="row mt-3">
                    <div class="col-12 mt-3">
                        <div class="h-100 shadowed_box_border_radius p-3">
                            <div class="font-weight-bold h4"> Conducteur</div>

                            <div class="w-100 d-flex flex-wrap justify-content-around align-items-center font-weight-bold mt-3">
                                <div class="col-12 grey_box_border_radius">
                                    <div class="row d-flex flex-wrap">
                                        <div class="">
                                            <img class="picture_driver_trip" src="{$driverPhoto}">
                                        </div>
                                        <div class="col d-flex align-items-center justify-content-center p-3">
                                            {$d->prenom} {$d->nom}
                                        </div>


                                        <div class="col p-2 ">
END;


        $dateAPlusOneDay = date('Y-m-d H:i:s', strtotime($dateT . ' +1 day'));
        //On détermine si on affiche les étoiles pour noter le conducteur
        $afficherNoteDriver = ($userIsDriver || $userIsCarpooler) && $userConnected != $d->idUser && strtotime($date) <= strtotime($dateAPlusOneDay);
        // $afficherNoteDriver = true;
        if ($afficherNoteDriver) {
            $res .= <<<END
                                            <div class="h-100 d-flex align-items-sm-start align-items-center justify-content-center">
END;
        } else {
            $res .= <<<END
                                            <div class="h-100 d-flex align-items-center justify-content-center">
END;
        }

        if ($notesMoyennes[$d->idUser] != User::NOTATION_DEFAULT) {
            //On affiche les étoiles pleines puis les étoiles vides
            for ($i = 1; $i <= $notesMoyennes[$d->idUser]; $i++) {
                $res .= <<<END
                                                <i class="bi bi-star-fill m-1"></i>
END;
            }
            for ($i = $notesMoyennes[$d->idUser] + 1; $i <= 5; $i++) {
                $res .= <<<END
                                                <i class="bi bi-star m-1"></i>
END;
            }
        } else {
            //On affiche des étoiles disabled
            $res .= <<<END
                                                <i class="bi bi-star disabled_star m-1"></i>
                                                <i class="bi bi-star disabled_star m-1"></i>
                                                <i class="bi bi-star disabled_star m-1"></i>
                                                <i class="bi bi-star disabled_star m-1"></i>
                                                <i class="bi bi-star disabled_star m-1"></i>
END;
        }
        $res .= <<<END
                                            </div>

END;
        if ($afficherNoteDriver) {
            $res .= <<<END
                                                <div class="w-100 d-none d-sm-flex align-items-center justify-content-center">
                                                    <div class="d-flex flex-column align-items-center justify-content-center position-absolute px-3 rate_carpooler_area">
                                                        <div class="pr-2">
                                                            Votre note
                                                        </div>
                                                        <div data-userid="{$d->idUser}" data-trip_id="{$t->idTrajet}" data-rate_url="{$routeParser->urlFor("rateCarpooler_post")}" class="font-size-1_2rem h-100 d-flex align-items-start justify-content-center">

END;

            if ($notesDonnees[$d->idUser] != User::NOTATION_DEFAULT) {
                for ($i = 1; $i <= $notesDonnees[$d->idUser]; $i++) {
                    $res .= <<<END
                                                            <i data-num_star="{$i}" class="bi bi-star-fill m-1 star_rating"></i>
END;
                }

                for ($i = $notesDonnees[$d->idUser] + 1; $i <= 5; $i++) {
                    $res .= <<<END
                                                            <i data-num_star="{$i}" class="bi bi-star m-1 star_rating"></i>
END;
                }
            } else {
                for ($i = 1; $i <= 5; $i++) {
                    $res .= <<<END
                                                            <i data-num_star="{$i}" class="bi bi-star m-1 disabled_star star_rating"></i>
END;
                }
            }

            $res .= <<<END

                                                        </div>
                                                    </div>
                                                </div>
END;
        }

        $res .= <<<END
                                        </div>


                                        <div class="col d-flex align-items-center justify-content-center p-3">
                                            {$driverSex}
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
END;

        $passagersHtml = "";
        if ($nbCarpooler > 0) {
            foreach ($p as $a) {
                //On détermine si on affiche les étoiles pour noter chaque covoitureur
                $afficherNoteCarpooler = ($userIsDriver || $userIsCarpooler) && $userConnected != $a->idUser && strtotime($date) <= strtotime($dateAPlusOneDay);
                $passagerPhoto = $a->photo ?: $routeParser->urlFor("img", ["routes" => "icons/defaultPP.jpg"]);
                $passagerSex = $a->sexe == 0 ? 'Femme' : 'Homme';

                $passagersHtml .= <<<END
            <div class="w-100 d-flex flex-wrap justify-content-around align-items-center font-weight-bold mt-3">
              <div class="col-12 grey_box_border_radius">
                  <div class="row d-flex flex-wrap">
                      <div class="">
                          <img class="picture_driver_trip" src="{$passagerPhoto}">
                      </div>
                      <div class="col d-flex align-items-center justify-content-center p-3">
                      {$a->prenom} {$a->nom}
                      </div>

                      <div class="col p-2 ">
END;

                if ($afficherNoteCarpooler) {
                    $passagersHtml .= <<<END
                          <div class="h-100 d-flex align-items-sm-start align-items-center justify-content-center">
END;
                } else {
                    $passagersHtml .= <<<END
                           <div class="h-100 d-flex align-items-center justify-content-center">
END;
                }

                if ($notesMoyennes[$a->idUser] != User::NOTATION_DEFAULT) {
                    //On affiche les étoiles pleines puis les étoiles vides
                    for ($i = 1; $i <= round($notesMoyennes[$a->idUser]); $i++) {
                        $passagersHtml .= <<<END
                                                <i class="bi bi-star-fill m-1"></i>
END;
                    }
                    for ($i = round($notesMoyennes[$a->idUser]) + 1; $i <= 5; $i++) {
                        $passagersHtml .= <<<END
                                                <i class="bi bi-star m-1"></i>
END;
                    }
                } else {
                    //On affiche des étoiles disabled
                    $passagersHtml .= <<<END
                                                <i class="bi bi-star disabled_star m-1"></i>
                                                <i class="bi bi-star disabled_star m-1"></i>
                                                <i class="bi bi-star disabled_star m-1"></i>
                                                <i class="bi bi-star disabled_star m-1"></i>
                                                <i class="bi bi-star disabled_star m-1"></i>
END;
                }

                $passagersHtml .= <<<END
                          </div>
END;

                if ($afficherNoteCarpooler) {
                    $passagersHtml .= <<<END
                          <div class="w-100 d-none d-sm-flex align-items-center justify-content-center">
                              <div class="d-flex flex-column align-items-center justify-content-center position-absolute px-3 rate_carpooler_area">
                                  <div class="pr-2">
                                        Votre note
                                  </div>
                                  <div data-userid="{$a->idUser}" data-trip_id="{$t->idTrajet}" data-rate_url="{$routeParser->urlFor("rateCarpooler_post")}" class="font-size-1_2rem h-100 d-flex align-items-start justify-content-center">


END;

                    if ($notesDonnees[$a->idUser] != User::NOTATION_DEFAULT) {
                        for ($i = 1; $i <= $notesDonnees[$a->idUser]; $i++) {
                            $passagersHtml .= <<<END
                                    <i data-num_star="{$i}" class="bi bi-star-fill m-1 star_rating"></i>
END;
                        }

                        for ($i = $notesDonnees[$a->idUser] + 1; $i <= 5; $i++) {
                            $passagersHtml .= <<<END
                                    <i data-num_star="{$i}" class="bi bi-star m-1 star_rating"></i>
END;
                        }
                    } else {
                        //On affiche des étoiles disabled
                        for ($i = 1; $i <= 5; $i++) {
                            $passagersHtml .= <<<END
                                    <i data-num_star="{$i}" class="bi bi-star m-1 disabled_star star_rating"></i>
END;
                        }
                    }


                    $passagersHtml .= <<<END

                                    </div>
                                </div>
                            </div>
END;

                }
                $passagersHtml .= <<<END
                      </div>


                      <div class="col d-flex align-items-center justify-content-center p-3">
                          {$passagerSex}
                      </div>
                  </div>
              </div>
            </div>
END;
            }
            $SplusieursCarpooler = count($p) > 1 ? 's' : '';
            $res .= <<<END
               <div class="row mt-3">
                   <div class="col-12 mt-3">
                       <div class="h-100 shadowed_box_border_radius p-3">
                           <div class="row">
                               <div class="col-12">
                                   <div class="font-weight-bold"> Passager{$SplusieursCarpooler}</div>
                                        {$passagersHtml}
                                   </div>
                               </div>
                           </div>
                       </div>
                   </div>
               </div>
END;
        }

        if (!$userIsDriver) {
            if($userIsNothingInTrip) {
                $message = "Envoyer un demande de participation au trajet au conducteur";
                $action = $routeParser->urlFor('askParticipation_post');
            }else{
                $action = $routeParser->urlFor('cancelParticipationTrip_post');
                if ($userIsCarpooler) {
                    $message = "Annuler ma participation";
                } elseif($userIsWaitingAnswerParticiaption) {
                    $message = "Annuler ma demande de participation";
                }
            }
            $res .= <<<END
                <form action="{$action}" name="consult_trip_form" data-method="post" onsubmit="return submit_form(event)">
                    <div class="row my-3">
                        <div class="col-12 mt-3">
                            <div class="h-100 shadowed_box_border_radius p-3">
                                <div class="row">
                                    <div class="col-12 col-md-9">
                                        <textarea type="text" size=200 class="form-control" name="msg"
                                                  placeholder="Message au conducteur"></textarea>

                                        <input type="text" size=200 class="form-control" name="id_trip"
                                                  value="{$t->idTrajet}" hidden>
                                    </div>
                                    <div class="col-6 col-md-3 d-flex justify-content-center">
                                        <input class="mt-2 mt-md-0 pt-4 pb-4 pt-md-0 pb-md-0 w-100 consult_trip_submit font-weight-bold text-white btn"
                                               type="submit" value="{$message}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
END;
        }
        $res .= <<<END
            </div>
        </div>
END;

        $res .= $gc->foot(["https://code.jquery.com/ui/1.12.1/jquery-ui.js",
            $routeParser->urlFor('js', ["routes" => "post_button.js"]),
            $routeParser->urlFor('js', ["routes" => "submit_form.js"]),
            $routeParser->urlFor('js', ["routes" => "modal.js"]),
            $routeParser->urlFor('js', ["routes" => "consult_trip.js"])]);
        return $res;
    }

    /**
     * Renvoie l'html de la page "Mes trajets"
     * @param $trajetsAVenir
     *                      tableau des trajets à venir contenant un tableau avec nom conducteur, sexe, note moyenne du conducteur, date du trajet, prix du trajet, trajet privé ou pas, départ, arrivée
     * @param $trajetsPasses
     *                      tableau des trajets passés contenant un tableau avec nom conducteur, sexe, note moyenne du conducteur, date du trajet, prix du trajet, trajet privé ou pas, départ, arrivée
     * @return string
     */
    public function renderMyTrips($trajetsWaitingAnswerParticipation,$trajetsAVenir, $trajetsPasses,$userConnected)
    {

        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();
        $gc = new GlobalController();
        $res = $gc->head("Mes trajets", [
            $routeParser->urlFor('css', ["routes" => "user_trips.css"]),
            $routeParser->urlFor('css', ["routes" => "modal.css"])
        ]);
        $res .= $gc->navbar();
        $res .= <<<END
        <div class="container-fluid first_element_page_with_menu h-100">
            <div class="row justify-content-center">
                <div class="d-flex justify-content-center col">
                    <div class="notch_title_page_fixed p-1 px-5 m-0">Mes trajets</div>
                </div>
            </div>

            <div class="d-flex flex-column pt-5">
                <div class="px-3 pt-3 mt-3 mb-5 box_list_my_trips">
                    <div class="mb-4 h5">
                        <div class="font-weight-bold text-center">Trajets à venir</div>
                        <hr class="hr_incoming_trip">
                    </div>
END;

        #On affiche chaque trajet à venir

        if (count($trajetsAVenir)==0) {
            $res .= <<<END
                <div class="text-center no_trips_my_trips px-4 py-2 mb-4"> Pas de trajets à venir </div>
END;
        }
        else{
            $res.=$this->renderListOfMyTrips($trajetsAVenir,$userConnected);
        }

        //on affiche les trajets en attente de réponse
        $res .= <<<END
                </div>
                <div class="px-3 pt-3 mb-5 box_list_my_trips">
                    <div class="mb-4 h5">
                        <div class="font-weight-bold text-center">Trajets auxquels vous voulez participer en attente de réponse</div>
                        <hr class="hr_incoming_trip">
                    </div>
END;


        #On affiche les trajets passés

        if (count($trajetsWaitingAnswerParticipation)==0){
            $res .= <<<END
                <div class="text-center no_trips_my_trips px-4 py-2 mb-4"> Pas de trajets en attente de réponse</div>
END;
        }
        else{
            $res.=$this->renderListOfMyTrips($trajetsWaitingAnswerParticipation,$userConnected);
        }

        $res .= <<<END
                </div>
                <div class="px-3 pt-3 mb-3 box_list_my_trips">
                    <div class="mb-4 h5">
                        <div class="font-weight-bold text-center">Anciens trajets</div>
                        <hr class="hr_incoming_trip">
                    </div>
END;


        #On affiche les trajets passés

        if (count($trajetsPasses)==0){
            $res .= <<<END
                <div class="text-center no_trips_my_trips px-4 py-2 mb-4"> Pas d'anciens trajets' </div>
END;
        }
        else{
            $res.=$this->renderListOfMyTrips($trajetsPasses,$userConnected);
        }

        $res .= <<<END
        </div>
    </div>
</div>
END;
        $res .= $gc->foot([
            $routeParser->urlFor('js', ["routes" => "post_button.js"]),
            $routeParser->urlFor('js', ["routes" => "modal.js"]),
            $routeParser->urlFor('js', ["routes" => "user_trips.js"])
        ]);
        return $res;
    }

    /**
     * Donne tous les elements div correspondant aux trajets d'une liste de trajets dans la page MyTrips
     * @param $trajets
     * @return string
     */
    private function renderListOfMyTrips($trajets,$userConnected){
        $res="";
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();
        foreach ($trajets as $t) {

            #Dates
            $arrayD = explode(" ", $t->dateD);
            $dateD = explode("-", $arrayD[0]);
            $dateD = $dateD[2] . "/" . $dateD[1] . "/" . $dateD[0];
            $hourD = substr($arrayD[1], 0, strlen($arrayD[1]) - 3);

            #Sexe du conducteur, départ et arrivée et date
            $sexe = ($t["sexe"] == 1) ? "Homme" : "Femme";

            #Photo
            if ($t["photo"] == null) {
                $photo = "../img/icons/defaultPP.jpg";
            } else {
                $photo = $t["photo"];
            }

            #Prix
            $prix = $t['prix'] . "€";

            $res .= <<<END
                <a href="{$routeParser->urlFor('consultTrip',['id'=>$t['idTrajet']])}" class="d-flex flex-row justify-content-around align-items-center incoming_trip p-3 mb-4">
                    <div class="row w-100 position-relative">
                        <div class="col-12 col-lg-5 pl-0">
                            <div class="d-flex flex-row align-items-center">
                                <img class="picture_driver_trip" src="{$photo}">

END;

            #On affiche le nom du conducteur
            $res .= <<<END
                                <div class="font-weight-bold d-flex flex-column justify-content-around align-items-center w-100">
                                    {$t["prenom"]}
                                <div>
END;

            //Note random
            $note = $t['note'];

            if ($note == -1) {
                $cpt = 0;
                while ($cpt != 5) {
                    $res .= <<<END
                <i class="bi bi-star disabled_star m-1 star_profile"></i>
END;
                    $cpt += 1;
                }

            } else {
                #On affiche la note du conducteur
                $note = round($note, 0, PHP_ROUND_HALF_EVEN);
                $cpt = 0;
                while ($cpt != $note) {
                    $res .= <<<END
                        <i class="bi bi-star-fill m-1 star_profile"></i>
END;
                    $cpt += 1;
                }
                while ($cpt != 5) {
                    $res .= <<<END
                        <i class="bi bi-star m-1 star_profile"></i>
END;
                    $cpt += 1;
                }
            }
            $lock = $t['listePrivee'] ? '
                                <img class="lock_picture mr-2" src="../img/icons/lock.png">' : '';

            #Sexe du conducteur, départ et arrivée et date
            $res .= <<<END
                    </div>
                                    {$sexe}
                                </div>
                            </div>
                        </div>
                        <div class="col-8 col-lg-6">
                            <div class="h-100 d-flex flex-row align-items-center justify-content-center flex-wrap">
                                <div class="p-2">
                                    Trajet de
                                </div>
                                <div class="p-2 font-weight-bold">
                                    {$t["villeD"]}
                                </div>
                                <div class="p-2">
                                    jusqu'à
                                </div>
                                <div class="p-2 font-weight-bold">
                                    {$t["villeA"]}
                                </div>
                                <div class="p-2">
                                    à
                                </div>
                                <div class="p-2 font-weight-bold">
                                    {$hourD}
                                </div>
                                <div class="p-2">
                                    le
                                </div>
                                <div class="p-2 font-weight-bold">
                                    {$dateD}
                                </div>
                            </div>
                        </div>
                        <div class="col-4 col-lg-1">
                            <div class="h-100 d-flex flex-row align-items-center justify-content-end mr-0 mr-lg-3">
                                {$lock}
                                <div class="font-weight-bold h5 price_result ">{$prix}</div>
                            </div>
                        </div>
END;
            //Afficher ce div si la date de départ du trajet est dans plus de 24h

            $arrayHeure = explode(":", $arrayD[1]);
            $arrayJour = explode("-", $arrayD[0]);
            #Date de départ
            $heure = $arrayHeure[0];
            $mois = $arrayJour[1];
            $jour = $arrayJour[2];
            $an = $arrayJour[0];
            $mkD = mktime($heure, 0, 0, $mois, $jour, $an);

            #date actuelle
            $mkT = mktime(date("H"), 0, 0, date("m"), date("d"), date("Y"));

            $diff = $mkD - $mkT;

            if($diff > 3600*24 && $t['conducteur'] != $userConnected->idUser){
//                <div data-modal_accept="{$routeParser->urlFor("cancelTrip_post")}" data-modal_accept_data='{ "idTrip" : $t->idTrajet}' class="modal_cancel_trip_consult_trip notch_right_page_absolute py-2 btn_grey_hover_black px-4 m-0 d-none d-md-inline-block"><div class="d-inline">Annuler le trajet</div></div>
                $res.= <<<END
                    <div data-modal_accept="{$routeParser->urlFor("cancelParticipationTrip_post")}" data-modal_accept_data='{ "id_trip" : {$t['idTrajet']}}'
                        class="modal_cancel_participation_my_trips position-absolute btn_hoverable icon_user_trips trash_icon d-flex justify-content-center align-items-center">
                        <i class="bi bi-trash-fill"></i>
                    </div>
END;
            }
            else{
                //Sinon afficher ce div si la date de fin du trajet est passée depuis moins de 24h
                $arrayA = explode(" ", $t['dateA']);
                $dateA = explode("-", $arrayA[0]);
                $heureA = explode(":", $arrayA[1]);

                $heure = $heureA[0];
                $mois = $dateA[1];
                $jour = $dateA[2];
                $an = $dateA[0];

                $mkA = mktime($heure, 0, 0, $mois, $jour, $an);

                $diff = $mkT - $mkA;

                if($diff < 3600*24 && $diff > 0){
                    $res.=<<<END
                        <div onclick="window.location='{$routeParser->urlFor('consultTrip',['id'=>$t['idTrajet']])}'"
                             class="position-absolute btn_hoverable icon_user_trips rate_user_icon d-flex justify-content-center align-items-center d-block">
                            <i class="bi bi-bookmark-star-fill"></i>
                        </div>
END;
                }
            }

            $res.=<<<END
                    </div>
                </a>
END;
        }
        return $res;
    }
}
