<?php


namespace Vroum\Utils;

class Utils
{

    private static $_instance;

    //Constructeur privé pour singleton
    private function __construct()
    {
    }

    //  Donne l'instance de la Crypt (Mise en place d'un Pattern Singleton)
    public static function getInstance(): Utils
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Utils();
        }
        return self::$_instance;
    }
}