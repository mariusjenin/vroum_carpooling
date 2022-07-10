<?php

namespace Vroum\View;


use Vroum\Controler\ConnectionManager;
use Vroum\Controler\GlobalController;
use Vroum\Model\User;
use Vroum\VroumApp;

class NotificationView
{

    public function __construct()
    {
    }

    public function renderNotificationSender($notifications)
    {
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();
        $gc = new GlobalController();
        $res = $gc->head("Mes notifications", [
            $routeParser->urlFor('css', ["routes" => "notifications.css"])
        ]);
        $res .= $gc->navbar();

        $res .= <<<END
        <div class="container-fluid first_element_page_with_menu h-100">
            <div class="row justify-content-center">
                <div class="d-flex justify-content-center col">
                    <div class="notch_title_page_fixed p-1 px-5 m-0">Mes notifications</div>
                </div>
            </div>
            <div class="row pt-5">
                <div class="col-12 col-md-10 col-lg-8 offset-0 offset-md-1 offset-lg-2 mt-4 d-flex justify-content-center align-items-center flex-wrap font-size-1_5rem">
END;

        if(count($notifications)>0) {
            foreach ($notifications as $key => $n) {
                $res .= <<<END
            <a href="{$routeParser->urlFor('allNotificationsFromUser', ['id' => $key])}" class="px-4 py-2 m-3 d-flex flex-row align-items-center justify-content-between btn_hoverable btn_notification_user">
            {$n['prenom']} {$n['nom']}
END;
                if ($n['nbNonLues'] > 0) {
                    $res .= <<<END
                <div class="bg_color_brand bubble_notifications_user text-white font-size-1_2rem text-center font-weight-bold ml-4 font-size-1_7rem">
                {$n['nbNonLues']}
                </div>
END;
                }
                $res .= '</a>';
            }
        } else {
            $res .= <<<END
                Pas de notification
END;
        }
        $res .= <<<END
                </div>
            </div>
        </div>
END;
        $res .= $gc->foot([]);
        return $res;
    }


    public function renderAllNotificationsFromUser($notifications, $user)
    {
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();


        $gc = new GlobalController();
        $res = $gc->head("Notifications reçues de $user->prenom $user->nom", [
            $routeParser->urlFor('css', ["routes" => "notifications.css"]),
            $routeParser->urlFor('css', ["routes" => "modal.css"])
        ]);
        $res .= $gc->navbar();
        $res .= <<<END
<div class="container-fluid first_element_page_with_menu h-100">
    <div class="d-flex justify-content-start">
        <a href="{$routeParser->urlFor('previous')}" class="notch_left_page_absolute pl-3 p-2 m-0 ">
            <div class="btn_hoverable previous_notifications_2">
                <img class="mw-100 mh-100" src="{$routeParser->urlFor('img', ["routes" => "icons/previous.png"])}"/>
            </div>
        </a>
    </div>
    <div class="row justify-content-center">
        <div class="d-flex justify-content-center col">
            <div class="notch_title_page_fixed p-1 px-5 m-0">Mes notifications</div>
        </div>
    </div>

    <div class="offset-lg-2 col-12 col-lg-8 pt-4">
        <div class="font-weight-bold mt-5 ml-5 px-3 pb-2 mb-0 h3 exped_notif">
            {$user->prenom}  {$user->nom}
        </div>
    </div>
    <div class="position-relative offset-lg-2 col-12 col-lg-8 d-flex flex-column list_notifications mb-3 pt-4 pl-4 pr-4">
END;

        $notifs_str="";
        $suppr_all_ready=false;
        foreach ($notifications as $n) {
            $date_notif = $new_format = date('d/m/Y', strtotime($n->date));
            $hour_notif = $new_format = date('H:i', strtotime($n->date));
            $notifs_str .= <<<END
        <div class="block_notif mb-4">
            <div class="position-relative d-flex flex-column p-4 block_notif_contenu">
                <div class="font-weight-bold h5">{$n->title}</div>
                {$n->content_notif}
END;
            if (!$n->lue) {
                $notifs_str .= <<<END
                <img class="position-absolute img_new_notification" src="{$routeParser->urlFor('img', ["routes" => "icons/warning_highlight.png"])}"/>
END;
            }
            $notifs_str .= <<<END
            </div>
            <div class="position-relative px-4 py-1 block_notif_date">
                Le {$date_notif} à {$hour_notif}
END;
            if($n->deletable) {
                $suppr_all_ready=true;
                $notifs_str .= <<<END
                <div data-modal_accept="{$routeParser->urlFor("deleteNotification_post")}" data-modal_accept_data='{ "id" : {$n->idNotif}}'  
                     class="modal_delete_notification position-absolute btn_hoverable trash_icon_notif d-flex justify-content-center align-items-center">
                    <i class="bi bi-trash-fill"></i>
                </div>
END;
            }
            $notifs_str .= <<<END
            </div>
        </div>
END;
        }
        if($suppr_all_ready) {
            $res .= <<<END
        <div data-modal_accept="{$routeParser->urlFor("deleteNotificationFromUser_post")}" data-modal_accept_data='{ "id" : {$user->idUser}}'       
             class="modal_delete_all_notifications position-absolute btn_hoverable trash_icon_all d-flex justify-content-center align-items-center">
            <i class="bi bi-trash-fill"></i>
        </div>
END;
        }
        $res .= $notifs_str;
        $res .= <<<END
    </div>
</div>
END;

        $res .= $gc->foot([
            $routeParser->urlFor('js', ["routes" => "modal.js"]),
            $routeParser->urlFor('js', ["routes" => "post_button.js"]),
            $routeParser->urlFor('js', ["routes" => "my_notifications_2.js"])
        ]);
        return $res;
    }
}
