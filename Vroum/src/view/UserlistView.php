<?php

namespace Vroum\View;


use Vroum\Controler\ConnectionManager;
use Vroum\Controler\GlobalController;
use Vroum\Controler\RedirectManager;
use Vroum\Model\User;
use Vroum\VroumApp;

class UserlistView
{

    public function __construct()
    {
    }

    public function renderUserlist($lists)
    {
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();
        $gc = new GlobalController();
        $res = $gc->head("Mes listes d'amis", [
            $routeParser->urlFor('css', ["routes" => "friend_list.css"]),
            $routeParser->urlFor('css', ["routes" => "search_trip.css"]),
            $routeParser->urlFor('css', ["routes" => "modal.css"])
        ]);
        $res .= $gc->navbar();

        $res .= <<<END
        <div class="container-fluid first_element_page_with_menu h-100">
            <div class="d-flex justify-content-center">
                <div class="notch_title_page_fixed p-2 px-5 m-0">Mes listes d'amis
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{$routeParser->urlFor('createUserList')}" class="notch_right_page_absolute p-2 pl-5 pr-3 m-0 d-none d-md-block "> Nouvelle
                    liste d'amis

                    <div class="position-absolute btn_hoverable add_trip d-flex justify-content-center align-items-center">
                        <img class="mw-100 mh-100" src="{$routeParser->urlFor('img', ["routes" => "icons/add_fill.png"])}"/>
                    </div>
                </a>
            </div>


           <div class="pt-5">
                    <div class="row mt-4">
END;
        $countlist = count($lists);
        if ($countlist == 0) {
            $res .= <<<END
                        <div class="text-center col-12 font-size-1_2rem">
                            Pas de listes d'amis
                        </div>
END;
        } else {

            foreach ($lists as $key => $l) {
                $nbMembers = count($l[1]);
                $nom = $l[0];
                $res .= <<<END
                        <div class="col-12 col-md-6 col-lg-4 my-3 order-0">
                            <div class="position-relative h-100 d-flex flex-column justify-content-around align-items-right shadowed_box_border_radius p-3">
                                <div class="font-weight-bold h4">
                                    {$nom}
                                </div>
                                <div class="h-100 w-100 d-flex flex-wrap flex-row justify-content-start align-items-center mt-2">
                                    <div class="p-2 align-items-right">
                                        Contient {$nbMembers} covoitureurs
                                    </div>
                                </div>
                                <div class="position-absolute block_icon_friend_list d-flex flex-row">
                                    <a href="{$routeParser->urlFor('editUserList', ["id" => $key])}"
                                         class="mr-2 btn_hoverable enter_icon_friend_list d-flex justify-content-center align-items-center">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <div data-modal_accept="{$routeParser->urlFor("deleteUserlist_post")}" data-modal_accept_data='{ "id" : {$key}}'
                                         class="modal_delete_user_list btn_hoverable trash_icon_friend_list d-flex justify-content-center align-items-center">
                                        <i class="bi bi-trash-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
END;

            }

        }
        $res .= <<<END
                    </div>
                </div>
END;


        $res .= $gc->foot([
            $routeParser->urlFor('js', ["routes" => "modal.js"]),
            $routeParser->urlFor('js', ["routes" => "post_button.js"]),
            $routeParser->urlFor('js', ["routes" => "user_list.js"])
        ]);
        return $res;
    }

    public function renderCreateOrEditList($list)
    {
        $isCreating = $list == null;
        $nom = $list['nom'] ?? "";
        $id = $list['id'] ?? "";
        $emails = $list['email'] ?? [];
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();

        $gc = new GlobalController();
        $res = $gc->head("Créer une liste d'amis", [
            $routeParser->urlFor('css', ["routes" => "create_friend_list.css"]),
            $routeParser->urlFor('css', ["routes" => "modal.css"])
        ]);
        $res .= $gc->navbar();
        $res .= <<<END
    <form name="form_friend_list" action="{$routeParser->urlFor('createOrEditUserList_post')}" data-method="post" onsubmit="return submit_form(event);"  class="container-fluid first_element_page_with_menu h-100">
        <div class="d-flex justify-content-start">
            <a href="{$routeParser->urlFor('previous')}" class="notch_left_page_absolute pl-3 p-2 m-0 ">
                <div class="btn_hoverable previous_user_list">
                    <img class="mw-100 mh-100" src="{$routeParser->urlFor('img', ["routes" => "icons/previous.png"])}"/>
                </div>
            </a>
        </div>

        <div class="d-flex justify-content-center">
            <div class="notch_title_page_fixed p-1 m-0">

                <input type="text" class="form-control title_input_friend_list text-center p-2 px-5" aria-required=true
                       name="name_list" placeholder="Nom de votre liste" value="{$nom}" required>
END;

        if (!$isCreating) {
            $res .= <<<END
                <div data-modal_accept="{$routeParser->urlFor("deleteUserlist_post")}" data-modal_accept_data='{ "id" : {$id}}'
                     class="modal_delete_user_list position-absolute btn_hoverable trash_users_list d-flex justify-content-center align-items-center">
                    <i class="bi bi-trash-fill"></i>
                </div>
END;
        }
        $res .= <<<END
            </div>
        </div>


        <div class="pt-5">
            <div class="row mt-4">
                <div class="pt-5 col-12 col-lg-8 offset-lg-2">
                    <div class="row">
                        <div class="col-10 offset-1 col-md-8 offset-md-2 col-xl-6 offset-xl-3 w-100 mb-5">
                            <div class="error_add_mail d-none text-center text-danger mb-2">
                                Vous devez ajouter une adresse mail
                            </div>
                            <div class="d-flex flex-row align-items-center justify-content-end ">
                                <input type="text" class="mail_input_friend_list d-inline form-control" aria-required=true
                                       placeholder="Email du covoitureur">
                                <div onclick="add_mail_to_list($(this).prev()[0].value,$(this).prev()[0],$($(this).parent().parent().parent().children()[1]));"
                                     class="btn_add_mail_friend_list">
                                    <img class="mw-100 mh-100" src="{$routeParser->urlFor('img', ["routes" => "icons/add_fill.png"])}"/>
                                </div>
                            </div>
                        </div>
                     <div class="col-12 d-flex flex-row flex-wrap justify-content-center align-items-center">
END;
        if (count($emails) > 0) {
            foreach ($emails as $e) {
                $res .= <<<END
                        <div class="mail_friend_list py-2 pl-3 pr-5 m-1 d-flex flex-row align-items-center justify-content-end">
                            <input type="hidden" value="{$e}" name="users_list[]">
                            {$e}
                            <div onclick='remove_mail_from_list($(this).parent());' class="btn_remove_mail_friend_list">
                                <img class="mw-100 mh-100" src="{$routeParser->urlFor('img', ["routes" => "icons/remove.png"])}"/>
                            </div>
                        </div>
END;
            }
        }

        $res .= <<<END
                </div>
                   <div class="col-12 text-center mt-5 mb-3">
END;
        if (!$isCreating){
        $res .= <<<END
            <input type="hidden" name="id_liste" value="{$id}">
END;
    }
        $res .= <<<END
                            <input class="px-5 py-3 submit_create_friend_list font-weight-bold text-white btn_hoverable btn"
                                   type="submit" value="Valider">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
END;
        $res .= $gc->foot([
            $routeParser->urlFor('js', ["routes" => "modal.js"]),
            $routeParser->urlFor('js', ["routes" => "post_button.js"]),
            $routeParser->urlFor('js', ["routes" => "submit_form.js"]),
            $routeParser->urlFor('js', ["routes" => "create_friend_list.js"])
        ]);
        return $res;
    }
}
