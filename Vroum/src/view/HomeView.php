<?php


namespace Vroum\View;


use Vroum\Controler\GlobalController;
use Vroum\VroumApp;

class HomeView
{

    public function __construct()
    {
    }

    /**
     * Renvoie l'html de la page Welcome
     * @return string
     */
    public function renderWelcome()
    {
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();
        $gc=new GlobalController();
        $res = $gc->head("Bienvenue sur Vroum",[$routeParser->urlFor('css',["routes"=>"welcome.css"])]);
        $res .= <<<END
<div class="background_gradient_animated">
    <div class="paral_welcome_1 d-none d-lg-block"> </div>
    <div class="paral_welcome_2 "> </div>
    <div class="paral_welcome_3 d-none d-lg-block"> </div>
    <div class="container-fluid mh-100 min-vh-100 d-flex justify-content-center align-items-center flex-column">

        <div class="row w-100 h-100">

            <div class="col-12 col-lg-5 order-1 order-lg-0 d-flex flex-column justify-content-center block_buttons_welcome">
                <a href="{$routeParser->urlFor('login')}">
                    <div class="buttons_welcome button_welcome_login">
                        Connexion
                    </div>
                </a>
                <a href="{$routeParser->urlFor('signup')}">
                    <div class="buttons_welcome button_welcome_signup">
                        Inscription
                    </div>
                </a>
            </div>

            <div class="opacity_09 col-12 col-lg-7 pb-5 mb-5 pb-lg-0 mb-lg-0 order-0 order-lg-1 d-flex flex-column justify-content-center ">
                <div class="block_title_welcome">
                    <div class="brand_title_welcome">
                        <div class="mr-5 d-flex align-items-center header_brand_navbar">
                            <img class="mw-100 mh-100 p-3 mr-2" src="{$routeParser->urlFor('img', ["routes" => "identity/logo_tab.png"])}">
                            VROUM
                        </div>
                    </div>
                    <div class="description_title_welcome">
                        Votre plateforme de covoiturage en ligne
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
END;
        $res .= $gc->foot([]);
        return $res;
    }

    /**
     * Renvoie l'html de la page Home
     * @return string
     */
    public function renderHome()
    {
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();
        $gc=new GlobalController();
        $res = $gc->head("Accueil", [$routeParser->urlFor('css',["routes"=>"accueil.css"])]);
        $res .= $gc->navbar();
        $res .= <<<END
        <div class="container-fluid first_element_page_with_menu no_scroll_page_vroum">
            <div class="row h-100">
                <div class="col-12 h-100">
                    <div class="row h-100">
                        <div class="col-6 col-lg-4 cell_card_home d-flex flex-column align-items-center">
                            <a class="d-flex flex-column align-items-center h-100 card_home" href="{$routeParser->urlFor('profile')}">
                                <div class="">
                                    Mon Profil
                                </div>
                                <img class="mh-100 img-fluid img_home mx-auto d-block"
                                     src="{$routeParser->urlFor('img', ["routes" => "section/profile_pic.jpg"])}">
                            </a>
                        </div>
                        <div class="col-6 col-lg-4 cell_card_home d-flex flex-column align-items-center">
                            <a class="d-flex flex-column align-items-center h-100 card_home" href="{$routeParser->urlFor('myTrips')}">
                                <div class="">
                                    Mes trajets
                                </div>
                                <img class="mh-100 img-fluid img_home mx-auto d-block"
                                     src="{$routeParser->urlFor('img', ["routes" => "section/my_trips_pic.jpg"])}">
                            </a>
                        </div>
                        <div class="col-6 col-lg-4 cell_card_home d-flex flex-column align-items-center">
                            <a class="d-flex flex-column align-items-center h-100 card_home" href="{$routeParser->urlFor('searchTrip')}">
                                <div class="">
                                    Rechercher un trajet
                                </div>
                                <img class="mh-100 img-fluid img_home mx-auto d-block"
                                     src="{$routeParser->urlFor('img', ["routes" => "section/search_trip_pic.jpg"])}">
                            </a>
                        </div>
                        <div class="col-6 col-lg-4 cell_card_home d-flex flex-column align-items-center">
                            <a class="d-flex flex-column align-items-center h-100 card_home" href="{$routeParser->urlFor('newTrip')}">
                                <div class="">
                                    Proposer un trajet
                                </div>
                                <img class="mh-100 img-fluid img_home mx-auto d-block"
                                     src="{$routeParser->urlFor('img', ["routes" => "section/create_trip_pic.jpg"])}">
                            </a>
                        </div>
                        <div class="col-6 col-lg-4 cell_card_home d-flex flex-column align-items-center">
                            <a class="d-flex flex-column align-items-center h-100 card_home" href="{$routeParser->urlFor('myUserLists')}">
                                <div class="">
                                    Mes listes d'amis
                                </div>
                                <img class="mh-100 img-fluid img_home mx-auto d-block"
                                     src="{$routeParser->urlFor('img', ["routes" => "section/users_lists_pic.jpg"])}">
                            </a>
                        </div>
                        <div class="col-6 col-lg-4 cell_card_home d-flex flex-column align-items-center">
                            <a class="d-flex flex-column align-items-center h-100 card_home" href="{$routeParser->urlFor("notificationsPerUser")}">
                                <div class="">
                                    Mes notifications
                                </div>
                                <img class="mh-100 img-fluid img_home mx-auto d-block"
                                     src="{$routeParser->urlFor('img', ["routes" => "section/notifications_pic.jpg"])}">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
END;
        $res .= $gc->foot([]);
        return $res;
    }
}
