<?php
namespace Vroum\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Vroum\Controler\ConnectionManager;
use Vroum\Controler\RedirectManager;
use Vroum\VroumApp;

class RedirectConnectedMiddleware
{
    /**
     * Redirect si l'utilisateur n'est pas connecté
     *
     * @param  ServerRequest  $request PSR-7 request
     * @param  RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $vroum=VroumApp::getInstance();
        $args= RouteContext::fromRequest($request)->getRoute()->getArguments();

        $response=new Response();
        $cm=ConnectionManager::getInstance();
        if($cm->getIdConnected()== false){
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $rm=RedirectManager::getInstance();
            $url=$rm->getUrlRedirect();
            return $response->withHeader('Location', $routeParser->urlFor($url["route"],$url["param"]));
        } else {
            $response = $handler->handle($request);
            return $response;
        }
    }
}
