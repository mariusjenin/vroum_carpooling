<?php


namespace Vroum\Controler;


class FileController
{
    public static function js($req, $resp, $args) {
        $resp->getBody()->write(file_get_contents("../js/" . $args['routes'] . ""));
        return $resp->withHeader('Content-Type', 'text/javascript');
    }


    public static function img($req, $resp, $args) {
        echo file_get_contents("../img/" . $args['routes'] . "");
        return $resp;
    }


    public static function fonts($req, $resp, $args) {
        echo file_get_contents("../fonts/" . $args['routes'] . "");
        return $resp;
    }


    public static function css($req, $resp, $args) {
        $resp->getBody()->write(file_get_contents("../css/" . $args['routes'] . ""));
        return $resp->withHeader('Content-Type', 'text/css');
    }


    public static function ajax($req, $resp, $args) {
        include "../src/ajax/" . $args['routes'] . "";
        return $resp->withHeader('Content-Type', 'application/json');
    }


    public static function uploads($req, $resp, $args) {
        $path = __DIR__ . "/../../uploads/" . $args['file'];

        if (file_exists($path)) {
            $resp->getBody()->write(file_get_contents($path) ?: '');
            return $resp->withStatus(200)
                ->withHeader('Content-Type', mime_content_type($path));
        } else {
            return $resp->withStatus(404);
        }
    }

}