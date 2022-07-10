<?php

namespace Vroum\Controler;

use Vroum\Model\User;
use Vroum\View\GlobalView;

class GlobalController
{
    public static function navbar()
    {
        $idConnected = ConnectionManager::getInstance()->getIdConnected();
        $userConnected = User::find($idConnected);
        $nbNotif = count($userConnected->notificationsSent()->where('lue','=','0')->get());
        $gv=new GlobalView();
        return $gv->renderNavbar($nbNotif);
    }

    public static function head($titletab, array $link)
    {
        $gv=new GlobalView();
        return$gv->renderHead($titletab, $link);
    }

    public static function foot(array $script)
    {
        $gv=new GlobalView();
        return $gv->renderFoot($script);
    }
}

?>
