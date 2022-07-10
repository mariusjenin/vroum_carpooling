<?php

namespace Vroum\View;

use Vroum\VroumApp;

class GlobalView
{

    public function __construct()
    {
    }

    /**
     * Renvoie l'entete html du document (avec les CSS ici)
     * @param $titletab
     * @param $link array
     * @return string
     */
    public function renderHead($titletab, array $link)
    {
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();
        $res = <<<END
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Site Title -->
    <title>{$titletab}</title>
    <!-- Meta Character Set -->
    <meta charset="UTF-8">
    <!-- Icon of Vroum -->
    <link rel="shortcut icon" href="{$routeParser->urlFor('img',["routes"=>"identity/logo_tab.png"])}">
    <!-- CSS For Bootstrap -->
    <link href="{$routeParser->urlFor('css',["routes"=>"bootstrap.min.css"])}" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link href="{$routeParser->urlFor('css',["routes"=>"color.css"])}" rel="stylesheet"/>
    <link href="{$routeParser->urlFor('css',["routes"=>"global.css"])}" rel="stylesheet"/>
END;
        foreach ($link as $l) {
            $res .= <<<END
<link href="{$l}" rel="stylesheet"/>
END;
        }
        $res .= <<<END
</head>
<body>
<div class="content">
END;
        return $res;
    }

    /**
     * Renvoie l'html de la navBar
     * @return string
     */
    public function renderNavbar($nbNotif)
    {
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();
        $res = <<<END
<nav class="fixed-top navbar navbar-expand navbar-dark bg-dark">
    <div class="ml-2 mr-5 d-flex align-items-center header_brand_navbar">
        <img class="mw-100 mh-100 p-2" src="{$routeParser->urlFor('img', ["routes" => "identity/logo_tab.png"])}">
        <a class="font-weight-bold navbar-brand ml-2 mr-4" href="{$routeParser->urlFor('home')}">Vroum</a>
    </div>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#globalnb" aria-controls="globalnb" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="globalnb">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item dropdown  align-self-lg-center ml-lg-3">
                <a class="dropdown-toggle nav-link" href="#" id="dropdown04" data-toggle="dropdown" data-display="static" aria-haspopup="true" aria-expanded="false">
                    <span class="text-navbar">
                        Menu
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg-left dropdown-menu-right" aria-labelledby="dropdown04">
                    <a class="dropdown-item" href="{$routeParser->urlFor("home")}">Accueil</a>
                    <a class="dropdown-item" href="{$routeParser->urlFor("profile")}">Profil</a>
                    <a class="dropdown-item" href="{$routeParser->urlFor("myTrips")}">Mes trajets</a>
                    <a class="dropdown-item" href="{$routeParser->urlFor('newTrip')}">Proposer un trajet</a>
                    <a class="dropdown-item" href="{$routeParser->urlFor("searchTrip")}">Rechercher un trajet</a>
                    <a class="dropdown-item" href="{$routeParser->urlFor("myUserLists")}">Mes listes d'amis</a>
                    <a class="dropdown-item" href="{$routeParser->urlFor("notificationsPerUser")}">Mes notifications</a>
                </div>
            </li>
        </ul>
    </div>
    <a href="{$routeParser->urlFor("notificationsPerUser")}" class="py-2 px-3 mr-2 d-flex flex-row align-items-center justify-content-between bg-white btn_hoverable btn_notification_navbar">
        Notifications
        <div class="bg_color_brand bubble_navbar_notifications text-white font-size-1_2rem text-center font-weight-bold ml-3">
            {$nbNotif}
        </div>
    </a>
</nav>
END;
        return $res;
    }

    /**
     * Renvoie le bas de page (listes des script JS ici)
     * @param $script array
     * @return string
     */
    public function renderFoot(array $script)
    {
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();
        $res = <<<END
</div>
<!-- JQuery-->
<script src="{$routeParser->urlFor('js',["routes"=>"jquery-3.5.1.min.js"])}"></script>
<!-- JavaScript Popper -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<!-- JavaScript Bootstrap -->
<script src="{$routeParser->urlFor('js',["routes"=>"bootstrap.min.js"])}"></script>
END;
        if(count($script)>0){
            $res .= <<<END
<!-- Custom JavaScript -->
END;
        }
        foreach ($script as $scr) {
            $res .= <<<END
<script src="{$scr}"></script>
END;
        }
        $res .= <<<END
</body>
</html>
END;
        return $res;
    }
}

?>
