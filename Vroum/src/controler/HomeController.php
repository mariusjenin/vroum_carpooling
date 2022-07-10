<?php

namespace Vroum\Controler;

use Vroum\View\HomeView;
use Slim\Psr7\Response;
use Vroum\VroumApp;

class HomeController {

    public static function welcome($req, Response $resp, $args) {
        $hv = new HomeView();
        $resp->getBody()->write($hv->renderWelcome());
        return $resp;
    }

    public static function home($req, Response $resp, $args) {
        RedirectManager::getInstance()->refreshCookieUrlRedirect($req,$args);
        $hv = new HomeView();
        $resp->getBody()->write($hv->renderHome());
        return $resp;
    }

    public static function previous($req, Response $resp, $args) {
        $url=RedirectManager::getInstance()->getUrlRedirect();
        return $resp->withHeader('Location', VroumApp::getInstance()->urlFor($url["route"],$url["param"]))->withStatus(302);
    }
}

?>
