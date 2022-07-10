<?php

namespace Vroum\View;

use Vroum\Controler\GlobalController;
use Vroum\Model\User;
use Vroum\VroumApp;

class IdentificationView
{

    public function __construct()
    {
    }

    /**
     * Renvoie l'html de la page d'inscription
     * @return string
     */
    public function renderSignup()
    {
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();
        $gc = new GlobalController();
        $res = $gc->head('Inscription', [$routeParser->urlFor('css', ["routes" => "signup_login_pwdforget.css"])]);
        $res .= <<<END
<div class="background_gradient_animated">
    <div class="paral_signup_login_pwdforget_1 d-none d-sm-block"> </div>
    <div class="paral_signup_login_pwdforget_2 d-none d-sm-block"> </div>
    <div class="paral_signup_login_pwdforget_3 d-none d-sm-block"> </div>
    <div class="container-fluid min-vh-100 d-flex justify-content-center align-items-center flex-column">
        <div class="d-flex mh-100 justify-content-between align-items-center">
            <div class="signup_block">
                <div class="h1 p-3 signup_login_pwdforget_title">
                    <a href="{$routeParser->urlFor('previous')}" class="mr-3 ml-1 back_signup_login">
                        <img class="mh-100 mw-100" src="{$routeParser->urlFor('img', ["routes" => "icons/previous.png"])}">
                    </a>
                    Inscription
                </div>
                <form action="{$routeParser->urlFor('signup_post')}" data-method="post" class="signup_login_pwdforget_form" onsubmit="return submit_form(event);" enctype="multipart/form-data">
                    <div class="row">

                        <div class="d-flex w-100 justify-content-center align-items-center pl-3 pr-3 mb-2">
                            Vous possédez déjà un compte ? Connectez-vous ici :
                            <a href="{$routeParser->urlFor('login')}" class="btn_signup_login ml-2">
                                <img class="mh-100 mw-100" src="{$routeParser->urlFor('img', ["routes" => "icons/right.png"])}">
                            </a>
                        </div>
                        <div class="error_form d-none w-100 text-danger font-weight-bold justify-content-center align-items-center pl-3 pr-3 mb-2">
                            Certaines valeurs ne conviennent pas
                        </div>
                        <div class="col-6" id="incriptionColLeft">
                            <div class="item_field_signup_login_pwdforget">
                                <label for="email" class="d-block font-weight-bold">Email *</label>
                                <input id="email" class="form-control d-block" type="email" placeholder="Votre email" name="email"
                                       required>
                            </div>

                            <div class="item_field_signup_login_pwdforget">
                                <label for="password" class="d-block font-weight-bold">Mot de passe *</label>
                                <input id="password" class="form-control d-block" type="password" name="password"
                                       placeholder="Votre mot de passe"
                                       required>
                            </div>

                            <div class="item_field_signup_login_pwdforget">
                                <label for="passwordConfirm" class="d-block font-weight-bold">Confirmez le mot de
                                    passe *</label>
                                <input id="passwordConfirm" class="form-control d-block" type="password" name="password_confirm"
                                       placeholder="Confirmez le mot de passe" required>
                            </div>


                            <div class="item_field_signup_login_pwdforget">
                                <label for="profilePic" class="d-block font-weight-bold">Ajoutez une image</label>
                                
                                <div class="d-flex flex-row align-items-center justify-content-center justify-content-sm-start">

                                    <div class="position-relative">
                                        <img class="profile_picture_signup_login_pwdforget" src="{$routeParser->urlFor('img', ["routes" => "icons/defaultPP.jpg"])}"/>

                                        <label for="profilePic"
                                               class="position-absolute m-0 btn_hoverable add_profile_picture d-flex justify-content-center align-items-center">
                                            <img class="mw-100 mh-100" src="{$routeParser->urlFor('img', ["routes" => "icons/add.png"])}"/>
                                        </label>
                                    </div>
                                    <input id="profilePic" class="form-control-file d-none" type="file"
                                           alt="Image de profil"
                                           accept="image/*" name="photo"
                                           onchange="loadfile(event)">

                                    <script type="text/javascript">
                                        var loadfile = function (event) {
                                            var img = document.getElementsByClassName("profile_picture_signup_login_pwdforget")[0];
                                            img.src = URL.createObjectURL(event.target.files[0]);
                                            img.onload = function () {
                                                URL.revokeObjectURL(img.src);  //Pour libérer la mémoire de l'ancienne image
                                            }
                                        };
                                    </script>
                                </div>
                            </div>
                        </div>


                        <div class="col-6" id="incriptionColRight">
                            <div class="item_field_signup_login_pwdforget">
                                <div class="d-flex">
                                    <div class="w-50 mr-1">
                                        <label for="name" class="d-block font-weight-bold">Prenom *</label>
                                        <input id="name" class="form-control d-block" type="text"
                                               placeholder="Votre prénom" name="first_name"
                                               required>
                                    </div>
                                    <div class="w-50 ml-1">
                                        <label for="surname" class="d-block font-weight-bold">Nom *</label>
                                        <input id="surname" class="form-control d-block" type="text"
                                               placeholder="Votre nom" name="last_name"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="item_field_signup_login_pwdforget">
                                <label class="font-weight-bold" for="masculin">Sexe *</label>
                                <div class="d-flex">
                                    <div class="w-50 mr-1">
                                        <input id="masculin" type="radio" name="sex" value="1" required>
                                        <label for="masculin">Masculin</label>
                                    </div>
                                    <div class="w-50 ml-1">
                                        <input id="feminin" type="radio" name="sex" value="0" required>
                                        <label for="feminin">Féminin</label>
                                    </div>
                                </div>
                            </div>

                            <div class="item_field_signup_login_pwdforget">
                                <label class="font-weight-bold" for="voitureOui">Possédez-vous une voiture ? *</label>
                                <div class="d-flex">
                                    <div class="w-50 mr-1">
                                        <input id="voitureOui" type="radio" name="has_car" value="1" required>
                                        <label for="voitureOui">Oui</label>
                                    </div>
                                    <div class="w-50 ml-1">
                                        <input id="voitureNon" type="radio" name="has_car" value="0" required>
                                        <label for="voitureNon">Non</label>
                                    </div>
                                </div>
                            </div>

                            <div class="item_field_signup_login_pwdforget">
                                <label class="font-weight-bold" for="telephone">Téléphone *</label>
                                <input class="form-control " id="telephone" type="text" name="phone_number"
                                       placeholder="Votre numéro de téléphone">
                            </div>

                        </div>

                        <div class="block_submit w-100 d-flex align-items-center flex-column text-center">
                            <button class="submit_signup_login_pwdforget btn_hoverable" type="submit">
                                <img class="mh-100 mw-100" src="{$routeParser->urlFor('img', ["routes" => "icons/check.png"])}">
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
END;
        $res .= $gc->foot([$routeParser->urlFor('js', ["routes" => "submit_form.js"])]);
        return $res;
    }

    /**
     * Renvoie l'html de la page de connexion
     * @return string
     */
    public function renderLogin()
    {
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();
        $gc = new GlobalController();
        $res = $gc->head('Connexion', [$routeParser->urlFor('css', ["routes" => "signup_login_pwdforget.css"])]);
        $res .= <<<END
<div class="background_gradient_animated">
    <div class="paral_signup_login_pwdforget_1 d-none d-sm-block"> </div>
    <div class="paral_signup_login_pwdforget_2 d-none d-sm-block"> </div>
    <div class="paral_signup_login_pwdforget_3 d-none d-sm-block"> </div>
    <div class="container-fluid min-vh-100  d-flex justify-content-center align-items-center flex-column">
        <div class="d-flex mh-100 justify-content-between align-items-center">
            <div class="login_block">
                <div class="h1 p-3 signup_login_pwdforget_title">
                    <a href="{$routeParser->urlFor('previous')}" class="mr-3 ml-1 back_signup_login">
                        <img class="mh-100 mw-100" src="{$routeParser->urlFor('img', ["routes" => "icons/previous.png"])}">
                    </a>
                    Connexion
                </div>
                <form action="{$routeParser->urlFor('login_post')}" data-method="post" class="signup_login_pwdforget_form" onsubmit="return submit_form(event);">
                    <div class="row">

                        <div class="d-flex w-100 justify-content-center align-items-center pl-3 pr-3 mb-2">
                            Vous ne possédez pas encore de compte ? Cliquez ici :
                            <a href="{$routeParser->urlFor('signup')}" class="btn_signup_login ml-2">
                                <img class="mh-100 mw-100" src="{$routeParser->urlFor('img', ["routes" => "icons/right.png"])}">
                            </a>
                        </div>
                        
                        <div class="d-flex w-100 justify-content-center align-items-center pl-3 pr-3 mb-2">
                            Mot de passe oublié ? Cliquez ici :
                            <a href="{$routeParser->urlFor('askToResetPassword')}" class="btn_signup_login ml-2">
                                <img class="mh-100 mw-100" src="{$routeParser->urlFor('img', ["routes" => "icons/key.png"])}">
                            </a>
                        </div>
                        
                        <div class="error_form d-none w-100 text-danger font-weight-bold justify-content-center align-items-center pl-3 pr-3 mb-2">
                            Certaines valeurs ne conviennent pas
                        </div>

                        <div class="col-12">
                            <div class="item_field_signup_login_pwdforget">
                                <label for="email" class="d-block font-weight-bold">Email </label>
                                <input id="email" class="form-control d-block" type="email" placeholder="Votre email" name="email"
                                       required>
                            </div>

                            <div class="item_field_signup_login_pwdforget">
                                <label for="password" class="d-block font-weight-bold">Mot de passe </label>
                                <input id="password" class="form-control d-block" type="password"
                                       placeholder="Votre mot de passe" name="password"
                                       required>
                            </div>
                        </div>

                        <div class="block_submit w-100 d-flex align-items-center flex-column text-center">
                            <button class="submit_signup_login_pwdforget btn_hoverable" type="submit">
                                <img class="mh-100 mw-100" src="{$routeParser->urlFor('img', ["routes" => "icons/check.png"])}">
                            </button>
                        </div>
                         
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
END;
        $res .= $gc->foot([$routeParser->urlFor('js', ["routes" => "submit_form.js"])]);
        return $res;
    }

    /**
     * Renvoie l'html de la page de profil
     * @param User $u
     * @return string
     */
    public function renderProfile(User $u)
    {
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();
        $gc = new GlobalController();

        $note=$u->moyenne;

        $checkedMasculin=$u->sexe == 1 ? 'checked' : '';
        $checkedFeminin=$u->sexe == 0 ? 'checked' : '';
        $checkedVoitureOui=$u->voiture == 1 ? 'checked' : '';
        $checkedVoiturNon=$u->voiture == 0 ? 'checked' : '';
        $recoitEmails = $u->recoit_email == 1 ? 'checked' : '';
        $photo = $u->photo ?: $routeParser->urlFor('img', ["routes" => "icons/defaultPP.jpg"]);

        $res = $gc->head('Mon profil', [
            $routeParser->urlFor('css', ["routes" => "profile.css"]),
            $routeParser->urlFor('css', ["routes" => "modal.css"])
        ]);
        $res .= $gc->navbar();
        $res .= <<<END
<div class="container-fluid first_element_page_with_menu min-vh-100 h-100">
     <div class="d-flex justify-content-center col">
        <div class="notch_title_page_fixed p-1 px-5 m-0">Mon profil

            <div data-modal_accept="{$routeParser->urlFor('profileDelete_post')}" class="modal_delete_profile position-absolute btn_hoverable trash_profile d-flex justify-content-center align-items-center">
                <i class="bi bi-trash-fill"></i>
            </div>
        </div>

    </div>
    <div class="d-flex justify-content-end">
        <div data-modal_accept="{$routeParser->urlFor('logout_post')}" class="modal_logout_profile notch_right_page_absolute py-2 btn_grey_hover_black px-4 m-0 d-none d-md-block">Deconnexion</div>
    </div>
        
    <form class="h-100 pb-3 font-size-1_2rem" action="{$routeParser->urlFor('profile_post')}" data-method="post" onsubmit="return submit_form(event);" enctype="multipart/form-data">
        <div class="pt-5 row h-100">
            <div class="error_form d-none w-100 text-danger font-weight-bold justify-content-center align-items-center pl-3 pr-3 mb-2">
                Certaines valeurs ne conviennent pas
            </div>
            <div class="col-12 col-lg-4 mt-3">
                <div class="d-flex flex-column justify-content-around h-100">
                    <div class="p-0 mb-2 d-flex flex column justify-content-center align-items-center">
                        <div class="position-relative">
                            <img class="profile_picture" src="{$photo}"/>

                            <label for="profilePic"
                                   class="position-absolute m-0 btn_hoverable modify_profile_picture d-flex justify-content-center align-items-center">
                                <img class="mw-100 mh-100" src="{$routeParser->urlFor('img', ["routes" => "icons/add.png"])}"/>
                            </label>
                        </div>
                        <input id="profilePic" class="form-control-file d-none" type="file"
                               alt="Image de profil" name="photo"
                               accept="image/*"
                               onchange="loadfile(event)">

                        <script type="text/javascript">
                            var loadfile = function (event) {
                                var img = document.getElementsByClassName("profile_picture")[0];
                                img.src = URL.createObjectURL(event.target.files[0]);
                                img.onload = function () {
                                    URL.revokeObjectURL(img.src);  //Pour libérer la mémoire de l'ancienne image
                                }
                            };
                        </script>
                    </div>
                    <div class="profil-note d-flex justify-content-center ">
END;

        if($note!=User::NOTATION_DEFAULT) {
            for ($i=1; $i <= $note; $i++) {
                $res .= <<<END
                        <i class="bi bi-star-fill m-1 star_profile"></i>
END;
            }

            for ($i=$note+1; $i <= 5; $i++) {
                $res .= <<<END
                        <i class="bi bi-star m-1 star_profile"></i>
END;
            }
        } else {
            //On affiche des étoiles disabled
            for ($i=1; $i <= 5; $i++) {
                $res .= <<<END
                        <i class="bi bi-star m-1 disabled_star star_profile"></i>
END;
            }
        }
        $res .= <<<END
                    </div>

                </div>
            </div>
            <div class="col-12 col-lg-8 mt-3">
                <div class="d-flex flex-column justify-content-around h-100">
                    <div class="profile_input d-flex flex-column align-items-center pt-3">
                        <div class="font-weight-bold">Profil</div>
                        <div class="row w-100">

                            <div class="col-6">
                                <div class="d-flex flex-column justify-content-around align-items-center ">
                                    <div class="w-100 my-2">
                                        <label for="first_name">Prénom</label>
                                        <input type="text" id="first_name" class="form-control" name="first_name"
                                               placeholder="Votre prénom"
                                               value="{$u->prenom}">
                                    </div>
                                    <div class="w-100 my-2">
                                        <label for="last_name">Nom</label>
                                        <input type="text" id="last_name" class="form-control" name="last_name"
                                               placeholder="Votre nom"
                                               value="{$u->nom}">
                                    </div>
                                    <div class="w-100 my-2">
                                        <label for="tel_num">Numéro de téléphone</label>
                                        <input type="text" id="tel_num" class="form-control w-100" name="tel_num"
                                               placeholder="Votre numéro de téléphone" value="{$u->tel}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex flex-column justify-content-around align-items-center ">
                                    <div class="w-100 my-2">
                                        <p class="mb-2">Sexe</p>
                                        <div class="d-flex justify-content-around py-1">
                                            <div class="form_check">
                                                <input type="radio" id="masculin" name="sex" value="1" {$checkedMasculin}>
                                                <label class="mb-0" for="masculin">Masculin</label>
                                            </div>
                                            <div class="form_check">
                                                <input type="radio" id="feminin" name="sex" value="0" {$checkedFeminin}>
                                                <label class="mb-0" for="feminin">Féminin</label>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="w-100 my-2">

                                        <p class="mb-2">
                                            Possédez-vous une voiture ?
                                        </p>
                                        <div class="d-flex justify-content-around py-1">
                                            <div class="form_check">
                                                <input type="radio" id="oui" name="car" value="1" {$checkedVoitureOui}>
                                                <label class="mb-0" for="oui">Oui</label>
                                            </div>
                                            <div class="form_check">
                                                <input type="radio" id="non" name="car" value="0" {$checkedVoiturNon}>
                                                <label class="mb-0" for="non">Non</label>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="w-100 my-2">
                                        <input type="checkbox" id="email-notification" name="email-notification" {$recoitEmails}>
                                        <label class="ml-2" for="email-notification">
                                            Recevoir des notification par mail
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row p-3 mt-3">
                        <div class="col-8 w-100">
                            <div class="profile_input p-3">
                                <div class="row">
                                    <div class="font-weight-bold text-center col-12">Changer de mot de passe ?</div>

                                    <div class="col-6 my-2">
                                        <label for="password">Mot de passe</label>
                                        <input type="password" id="password" class="form-control" name="password"
                                               placeholder="Mot de passe">
                                    </div>
                                    <div class="col-6 my-2">
                                        <label for="password_confirm">Confirmation</label>
                                        <input type="password" id="password_confirm" class="form-control"
                                               name="password_confirm"
                                               placeholder="Confirmez votre mot de passe">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4 w-100 h-100 ">
                            <div class="h-100 d-flex flex-column justify-content-center align-items-center">
                                <input class="mt-2 mt-md-0 pt-4 pb-4 pt-md-0 pb-md-0 h-100 w-100 profile_submit font-weight-bold text-white btn"
                                       type="submit" value="Valider les modifications">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
END;
        $res .= $gc->foot([$routeParser->urlFor('js', ["routes" => "post_button.js"]),
            $routeParser->urlFor('js', ["routes" => "submit_form.js"]),
            $routeParser->urlFor('js', ["routes" => "modal.js"]),
            $routeParser->urlFor('js', ["routes" => "profile.js"])]);
        return $res;
    }

    /**
     * Renvoie l'html de la page de réinitialisation de mot de passe (2)
     * @return string
     */

    public function renderResetPassword($link)
    {
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();
        $gc = new GlobalController();
        $res = $gc->head('Reinitialisation du mot de passe', [$routeParser->urlFor('css', ["routes" => "signup_login_pwdforget.css"])]);
        $res .= <<<END
<div class="background_gradient_animated">
    <div class="paral_signup_login_pwdforget_1 d-none d-sm-block"> </div>
    <div class="paral_signup_login_pwdforget_2 d-none d-sm-block"> </div>
    <div class="paral_signup_login_pwdforget_3 d-none d-sm-block"> </div>
    <div class="container-fluid d-flex justify-content-center align-items-center flex-column">
        <div class="d-flex vh-100 justify-content-between align-items-center">
            <div class="login_block">
                <div class="h1 p-3 signup_login_pwdforget_title">
                    Mot de passe oublié
                </div>
                <form action="{$routeParser->urlFor('resetPasswordFromLink_post')}" data-method="post" class="signup_login_pwdforget_form" onsubmit="return submit_form(event);">
                    <input name="link" value="{$link}" type="text" hidden required>
                    <div class="row">
                        <div class="d-flex w-100 justify-content-center align-items-center pl-3 pr-3 mb-2">
                            Insérez votre nouveau mot de passe :
                        </div>
                        <div class="error_form d-none w-100 text-danger font-weight-bold justify-content-center align-items-center pl-3 pr-3 mb-2">
                            Certaines valeurs ne conviennent pas
                        </div>
                        <div class="col-12">
                            <div class="item_field_signup_login_pwdforget">
                                <label for="password" class="d-block font-weight-bold">Votre nouveau mot de passe</label>
                                <input id="password" name="password" class="form-control d-block" type="password" placeholder="Nouveau mot de passe"
                                       required>
                            </div>
                            <div class="item_field_signup_login_pwdforget">
                                <label for="password_confirm" class="d-block font-weight-bold">Confirmation de votre nouveau mot de passe</label>
                                <input id="password_confirm" name="password_confirm" class="form-control d-block" type="password" placeholder="Confirmation du mot de passe"
                                       required>
                            </div>

                            <div class="block_submit w-100 d-flex align-items-center flex-column text-center">
                            <button class="submit_signup_login_pwdforget btn_hoverable" type="submit">
                                <img class="mh-100 mw-100" src="{$routeParser->urlFor('img', ["routes" => "icons/check.png"])}">
                            </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
END;
        $res .= $gc->foot([$routeParser->urlFor('js', ["routes" => "submit_form.js"])]);
        return $res;
    }

    /**
     * Renvoie l'html de la page de réinitialisation de mot de passe (1)
     * @return string
     */

    public function renderAskToResetPassword()
    {
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();
        $gc = new GlobalController();
        $res = $gc->head('Demande de reinitialisation du mot de passe', [$routeParser->urlFor('css', ["routes" => "signup_login_pwdforget.css"])]);
        $res .= <<<END
<div class="background_gradient_animated">
    <div class="paral_signup_login_pwdforget_1 d-none d-sm-block"> </div>
    <div class="paral_signup_login_pwdforget_2 d-none d-sm-block"> </div>
    <div class="paral_signup_login_pwdforget_3 d-none d-sm-block"> </div>
    <div class="container-fluid d-flex justify-content-center align-items-center flex-column">

        <div class="d-flex vh-100 justify-content-between align-items-center">
            <div class="pwdForget_block">
                <div class="h2 p-3 signup_login_pwdforget_title">
                    <a href="{$routeParser->urlFor('previous')}" class="mr-3 ml-1 back_signup_login">
                        <img class="mh-100 mw-100" src="{$routeParser->urlFor('img', ["routes" => "icons/previous.png"])}">
                    </a>
                    Récupérez votre compte
                </div>
                <form action="{$routeParser->urlFor('resetPassword_post')}" data-method="post" class="signup_login_pwdforget_form" onsubmit="return submit_form(event);">

                    <div class="row">
                        <div class="d-flex w-100 justify-content-center align-items-center pl-3 pr-3 mb-2">
                            Insérez votre adresse mail pour recevoir un mail afin que vous puissiez définir un nouveau mot de passe :
                        </div>
                        <div class="error_form d-none w-100 text-danger font-weight-bold justify-content-center align-items-center pl-3 pr-3 mb-2">
                            Certaines valeurs ne conviennent pas
                        </div>
                        <div class="col-12">
                            <div class="item_field_signup_login_pwdforget">
                                <label for="mail" class="d-block font-weight-bold">Votre adresse mail</label>
                                <input id="mail" name="mail" class="form-control d-block" type="email" placeholder="Mail" required>
                            </div>
                        </div>

                        <div class="block_submit w-100 d-flex align-items-center flex-column text-center">
                            <button class="submit_signup_login_pwdforget btn_hoverable" type="submit">
                                <img class="mh-100 mw-100" src="{$routeParser->urlFor('img', ["routes" => "icons/check.png"])}">
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
END;
        $res .= $gc->foot([$routeParser->urlFor('js', ["routes" => "submit_form.js"])]);
        return $res;
    }

    public function renderAskToResetPasswordDone($user)
    {
        $routeParser = VroumApp::getInstance()->getApp()->getRouteCollector()->getRouteParser();
        $gc = new GlobalController();
        $res = $gc->head('Connexion', [$routeParser->urlFor('css', ["routes" => "signup_login_pwdforget.css"])]);
        $res .= <<<END
            <div class="background_gradient_animated">
                <div class="paral_signup_login_pwdforget_1 d-none d-sm-block"> </div>
                <div class="paral_signup_login_pwdforget_2 d-none d-sm-block"> </div>
                <div class="paral_signup_login_pwdforget_3 d-none d-sm-block"> </div>
            
                <div class="container-fluid d-flex justify-content-center align-items-center flex-column">
                    <div class="d-flex vh-100 justify-content-between align-items-center">
                        <div class="pwdForget_block">
                            <div class="h1 p-2 text-center signup_login_pwdforget_title">
                                Mail envoyé
                            </div>
                            <div class="signup_login_pwdforget_neutral">
                                Vous avez reçu un mail à l'adresse {$user->email}. Vous y trouverez un lien afin que vous puissiez définir un nouveau mot de passe.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
END;
        $res .= $gc->foot([]);
        return $res;
    }
}


?>
