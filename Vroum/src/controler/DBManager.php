<?php

namespace Vroum\Controler;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Vroum\Model\Notification;
use Vroum\Observer\NotificationObserver;

include_once __DIR__ . '/../../config/config.php';

class DBManager {
    public static function setup() {
        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => DBHOST,
            'port'      => DBPORT,
            'database'  => DBNAME,
            'username'  => DBUSER,
            'password'  => DBPWD,
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);

        // set the event dispatcher used by Eloquent models
        $capsule->setEventDispatcher(new Dispatcher(new Container));

        // make the capsule a global singleton
        $capsule->setAsGlobal();

        // setup Eloquent
        $capsule->bootEloquent();


        Notification::observe(NotificationObserver::class);
    }
}

?>
