<?php


namespace Vroum;

use DI\Container;
use Selective\BasePath\BasePathMiddleware;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Exception\HttpNotFoundException;
use Vroum\Controler\RedirectManager;
use Vroum\Middleware\RedirectConnectedMiddleware;
use Vroum\Controler\IdentificationController;
use Vroum\Middleware\RedirectNotConnectedMiddleware;

/**
 * Classe qui se charge du routage de l'application web Vroum
 *
 * Class VroumApp
 *
 * @package Vroum
 */
class VroumApp
{
    private static $_instance;

    private $app;

    private function __construct()
    {
        $container = new Container();
        AppFactory::setContainer($container);

        $app = AppFactory::create();
        // Add Slim routing middleware
        $app->addRoutingMiddleware();
        // To automatically parse PUT bodies
        $app->addBodyParsingMiddleware();

        // Set the base path to run the app in a subdirectory.
        // This path is used in urlFor().
        $app->add(new BasePathMiddleware($app));

        $vroum = $this;

        // Define Custom Error Handler
        $notFoundhandler = function (
//            ServerRequestInterface $request,
//            Throwable $exception,
//            bool $displayErrorDetails,
//            bool $logErrors,
//            bool $logErrorDetails
        ) use ($vroum) {
            //On redirige la page vers l'url de redirection en cas de not found
            $rm=RedirectManager::getInstance();
            $url = $rm->getUrlRedirect();
            $response = $vroum->app->getResponseFactory()->createResponse();
            $routeParser = $vroum->app->getRouteCollector()->getRouteParser();
            return $response->withHeader('Location', $routeParser->urlFor($url["route"], $url["param"]));
        };

        // Add Error middleware
        $errorMiddleware = $app->addErrorMiddleware(true, true, true);
        // On enregistre un errorMiddleware pour catch les notFound
        $errorMiddleware->setErrorHandler(HttpNotFoundException::class, $notFoundhandler);


        $this->app = $app;
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            $_SERVER["ROOT_PATH"] = __DIR__ . "/../Vroum";
            self::$_instance = new VroumApp();
        }
        return self::$_instance;
    }

    public function siteURL() {
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ||
            $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'];
        return $protocol.$domainName;
    }

    //Routes du site
    public function addRoutes()
    {

        //Obligé de mettre l'instance dans une variable pour la passer en "use" des methodes de routage

        $this->app->add(function ($request, $handler) {
            $response = $handler->handle($request);
            return $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                ->withHeader('X-Content-Type-Options', 'nosniff');
        });

        //########################## GET #########################

        //Accueil non connecté
        $this->app->get('/welcome', 'Vroum\Controler\HomeController::welcome')->setName('welcome')
            ->add(new RedirectNotConnectedMiddleware());

        //Accueil connecté
        $this->app->get('/home', 'Vroum\Controler\HomeController::home')->setName('home')
            ->add(new RedirectConnectedMiddleware());

        //Retour en arrière
        $this->app->get('/previous', 'Vroum\Controler\HomeController::previous')->setName('previous');

        //Page d'inscription
        $this->app->get('/signup', 'Vroum\Controler\IdentificationController::signup')->setName('signup')
            ->add(new RedirectNotConnectedMiddleware());

        //Page de connexion
        $this->app->get('/login', 'Vroum\Controler\IdentificationController::login')->setName('login')
            ->add(new RedirectNotConnectedMiddleware());

        //Page de profil
        $this->app->get('/profile', 'Vroum\Controler\IdentificationController::profile')->setName('profile')
            ->add(new RedirectConnectedMiddleware());

        //Création de trajet
        $this->app->get('/trip/create', 'Vroum\Controler\TripController::createTrip')->setName('newTrip')
            ->add(new RedirectConnectedMiddleware());

        //Recherche de trajet
        $this->app->get('/trip/search', 'Vroum\Controler\TripController::searchTrip')->setName('searchTrip')
            ->add(new RedirectConnectedMiddleware());

        //Consulter un trajet
        $this->app->get('/trip/consult/{id:[0-9]+}', 'Vroum\Controler\TripController::consultTrip')->setName('consultTrip')
            ->add(new RedirectConnectedMiddleware());

        //Récupérer toutes les notifications triées par expéditeur
        $this->app->get('/notifications', 'Vroum\Controler\NotificationController::notificationsPerUser')->setName('notificationsPerUser')
            ->add(new RedirectConnectedMiddleware());

        //Récupérer toutes les notifications liées à un expéditeur
        $this->app->get('/notifications/{id:[0-9]+}', 'Vroum\Controler\NotificationController::allNotificationsFromUser')->setName('allNotificationsFromUser')
            ->add(new RedirectConnectedMiddleware());

	    //Trajets de l'utilisateur
        $this->app->get('/my-trips', 'Vroum\Controler\TripController::myTrips')->setName('myTrips')
            ->add(new RedirectConnectedMiddleware());

        //Rentrer son mail pour l'envoi du mail de récupération de mot de passe
        $this->app->get('/reset-password', 'Vroum\Controler\IdentificationController::askToResetPassword')->setName('askToResetPassword')
            ->add(new RedirectNotConnectedMiddleware());

        //Page affirmant le bon envoi du mail de récupération de mot de passe
        $this->app->get('/reset-password/sent/{id:.+}', 'Vroum\Controler\IdentificationController::askToResetPasswordDone')->setName('askToResetPasswordDone')
            ->add(new RedirectNotConnectedMiddleware());

        //Page permettant de redéfinir son mot de passe
        $this->app->get('/reset-password/{link:.+}', 'Vroum\Controler\IdentificationController::resetPassword')->setName('resetPassword')
            ->add(new RedirectNotConnectedMiddleware());

        //Page permettant de visualiser toutes ses listes de diffusion
        $this->app->get('/userlist/my', 'Vroum\Controler\UserlistController::myLists')->setName('myUserLists')
            ->add(new RedirectConnectedMiddleware());

        //Page permettant de créer une liste d'amis
        $this->app->get('/userlist/create', 'Vroum\Controler\UserlistController::createUserList')->setName('createUserList')
            ->add(new RedirectConnectedMiddleware());

        //Page permettant de modifier une liste d'amis
        $this->app->get('/userlist/edit/{id:.+}', 'Vroum\Controler\UserlistController::editUserList')->setName('editUserList')
            ->add(new RedirectConnectedMiddleware());

        //######################### POST #########################

        //Post d'inscription
        $this->app->post('/signup', 'Vroum\Controler\IdentificationController::signup_post')->setName('signup_post')
            ->add(new RedirectNotConnectedMiddleware());

        //Post de connexion
        $this->app->post('/login', 'Vroum\Controler\IdentificationController::login_post')->setName('login_post')
            ->add(new RedirectNotConnectedMiddleware());

        //Post de déconnexion
        $this->app->post('/logout', 'Vroum\Controler\IdentificationController::disconnect_post')->setName('logout_post')
            ->add(new RedirectConnectedMiddleware());

        //Post de création de trajet
        $this->app->post('/trip/create', 'Vroum\Controler\TripController::createTrip_post')->setName('newTrip_post')
            ->add(new RedirectConnectedMiddleware());

        //Post pour la recherche de trajet
        $this->app->post('/trip/search', 'Vroum\Controler\TripController::searchTrip_post')->setName('searchTrip_post')
            ->add(new RedirectConnectedMiddleware());

        //Post du profil
        $this->app->post('/profile/modify', 'Vroum\Controler\IdentificationController::profile_post')->setName('profile_post')
            ->add(new RedirectConnectedMiddleware());

        //Post pour la suppression de profil
        $this->app->post('/profile/delete', 'Vroum\Controler\IdentificationController::profileDelete_post')->setName('profileDelete_post')
            ->add(new RedirectConnectedMiddleware());

        //Post pour l'annulation d'un trajet
        $this->app->post('/trip/cancel', 'Vroum\Controler\TripController::cancelTrip_post')->setName('cancelTrip_post')
            ->add(new RedirectConnectedMiddleware());

        //Noter un covoitureur
        $this->app->post('/trip/rate-carpooler', 'Vroum\Controler\RatingController::rateCarpooler_post')->setName('rateCarpooler_post')
            ->add(new RedirectConnectedMiddleware());

        //Supprimer une notification
        $this->app->post('/notifications/delete', 'Vroum\Controler\NotificationController::deleteNotification_post')->setName('deleteNotification_post')
            ->add(new RedirectConnectedMiddleware());

        //Supprimer les notifications provenant d'un Utilisateur donné
        $this->app->post('/notifications/delete-from-user', 'Vroum\Controler\NotificationController::deleteNotificationFromUser_post')->setName('deleteNotificationFromUser_post')
            ->add(new RedirectConnectedMiddleware());

        //Envoi d'un mail pour réinitialiser son mot de passe
        $this->app->post('/reset-password/mail', 'Vroum\Controler\IdentificationController::resetPassword_post')->setName('resetPassword_post')
            ->add(new RedirectNotConnectedMiddleware());

        //Réinitialisation du mot de passe
        $this->app->post('/reset-password', 'Vroum\Controler\IdentificationController::resetPasswordFromLink_post')->setName('resetPasswordFromLink_post')
            ->add(new RedirectNotConnectedMiddleware());

        //Demande de participation d'un covoitureur à un trajet
        $this->app->post('/trip/ask-participation', 'Vroum\Controler\TripController::askParticipation_post')->setName('askParticipation_post')
            ->add(new RedirectConnectedMiddleware());

        //Répondre à la demande de participation d'un covoitureur
        $this->app->post('/trip/participate', 'Vroum\Controler\TripController::participateToTrip_post')->setName('participateToTrip_post')
            ->add(new RedirectConnectedMiddleware());

        //Annuler la participation/la demande de participation à un trajet
        $this->app->post('/trip/participate/cancel', 'Vroum\Controler\TripController::cancelParticipationTrip_post')->setName('cancelParticipationTrip_post')
            ->add(new RedirectConnectedMiddleware());

        //Créer/Modifier une nouvelle liste de diffusion
        $this->app->post('/userlist/edit', 'Vroum\Controler\UserlistController::createOrEditList_post')->setName('createOrEditUserList_post')
            ->add(new RedirectConnectedMiddleware());

        //Supprimer une liste de diffusion
        $this->app->post('/userlist/delete', 'Vroum\Controler\UserlistController::deleteList_post')->setName('deleteUserlist_post')
            ->add(new RedirectConnectedMiddleware());

        //########################## ROUTE GET POUR LES FICHIERS #########################

        //route pour les fichiers js
        $this->app->get('/js/{routes:.+}','Vroum\Controler\FileController::js')->setName('js');

        //Route pour les images
        $this->app->get('/img/{routes:.+}','Vroum\Controler\FileController::img')->setName('img');

        //Route pour les fonts
        $this->app->get('/fonts/{routes:.+}', 'Vroum\Controler\FileController::fonts')->setName('fonts');

        //Route pour le css
        $this->app->get('/css/{routes:.+}', 'Vroum\Controler\FileController::css')->setName('css');

        //Routes pour les requetes ajax (json)
        $this->app->map(['GET', 'POST'], '/ajax/{routes:.+}', 'Vroum\Controler\FileController::ajax');

        //Routes pour les uploads de fichiers
        $this->app->get('/uploads/{file:.+}','Vroum\Controler\FileController::uploads')->setName('uploads');

    }

    //Ajoute les routes pour les tests (TODO à retirer dans la version finale du site)
    public function addRoutesTest()
    {

        //False root
        $this->app->get('/root', function (Request $request, Response $response) {

            $response->getBody()->write(
                '<h1>ROOT</h1>' .
                '<a style="display: block" href="accueil">Site</a>' .
                '<a style="display: block" href="test">test</a>' .
                '<a style="display: block" href="designdevroot">Design Dev</a>'
            );
            return $response;
        })->setName('root');

        //test
        $this->app->get('/test', function (Request $request, Response $response) {
            $response->getBody()->write("<h1>TEST</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/root';\">Return to ROOT</button>"
            );
            $response->getBody()->write('<br>');

            IdentificationController::login_post($request, $response, []);

            return $response;
        })->setName('test');

        $this->app->post('/test/signup', 'Vroum\Controler\IdentificationController::signup_post')->setName('test_signup_post');

        $this->app->post('/test/login', 'Vroum\Controler\IdentificationController::login_post')->setName('test_login_post');

        $this->app->post('/test/trip/create', 'Vroum\Controler\TripController::createTrip_post')->setName('newTrip_post');


    }

    //Ajoute les routes pour le developpement des vues (TODO à retirer dans la version finale du site)
    public function addRoutesDesignDev()
    {
        //designdev
        $this->app->get('/designdevroot', function (Request $request, Response $response) {
            $response->getBody()->write("<h1>DesignDev</h1>" .
                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/root';\">Return to ROOT</button>"
            );

            $dir    = '../designdev';
            $files = scandir($dir);
            for($i=0;$i<count($files);$i++){
                if($files[$i]!='.' && $files[$i]!='..'){
                    $response->getBody()->write('<a style="display: block" href="designdev/'.$files[$i].'">'.$files[$i].'</a>');
                }
            }

            return $response;
        })->setName('designdev');

        //css
        $this->app->get('/designdev/css/{routes:.+}', function (Request $request, Response $response, $args) {
            $response->getBody()->write(file_get_contents("../css/" . $args['routes'] . ""));
            return $response->withHeader('Content-Type', 'text/css');
        });

        //js
        $this->app->get('/designdev/js/{routes:.+}', function (Request $request, Response $response, $args) {
            $response->getBody()->write(file_get_contents("../js/" . $args['routes'] . ""));
            return $response->withHeader('Content-Type', 'text/javascript');
        });

        //Route pour les images
        $this->app->get('/designdev/img/{routes:.+}', function (Request $request, Response $response, $args) {
            echo file_get_contents("../img/" . $args['routes'] . "");
            return $response;
        });

        //Route pour les fonts
        $this->app->get('/designdev/fonts/{routes:.+}', function (Request $request, Response $response, $args) {
            echo file_get_contents("../fonts/" . $args['routes'] . "");
            return $response;
        });

        //ajax(json)
        $this->app->get('/designdev/ajax/{routes:.+}', function (Request $request, Response $response, $args) {
            include "../src/ajax/" . $args['routes'] . "";
            return $response->withHeader('Content-Type', 'application/json');
        });

        //designdev
        $this->app->get('/designdev/{routes:.+}', function (Request $request, Response $response, $args) {
            echo file_get_contents("../designdev/" . $args['routes'] . "");
            return $response;
        })->setName('designdev_routes');
    }

    public function run()
    {
        // Run app
        $this->app->run();
    }

    /**
     * @return App
     */
    public function getApp()
    {
        return $this->app;
    }

    public function urlFor($route, $args = []) {
        return $this->app->getRouteCollector()->getRouteParser()->urlFor($route, $args);
    }
}
