<?php

namespace Vroum\Controler;

use Slim\Psr7\Request;
use Slim\Psr7\UploadedFile;
use Vroum\Model\User;
use Vroum\View\IdentificationView;
use Vroum\VroumApp;
use Slim\Psr7\Response;
use Vroum\Utils\Crypt;

class IdentificationController {
    /**
     * A set of symbols allowed in a password. According to the specification,
     * there must be at least one of them in every password.
     *
     * @internal
     * @var string
     */
    private const SYMBOL_SET = '!/:;.?,@$&~#|^_-*+=\\';
    /**
     * A set of digits allowed in a password (basically every digits). There must be
     * at least one of them in every password.
     *
     * @internal
     * @var string
     */
    private const DIGIT_SET = '0123456789';

    /**
     * A very simple function used to automatically determine which hash algorithm
     * to use depending on whether a specific PHP installation supports it, or not.
     *
     * It also hashes the password given using the determined algorithm.
     *
     * @param string $pass The password to hash
     *
     * @return string The hashed password. Note that it is unnecessary to return the algorithm used
     *                because PHP's `password_verify` can infer it
     */
    private static function hash($pass) {
        // if our PHP supports it, use Argon2ID, else Argon2I if not,
        // and fallback to BCrypt if Argon2I isn't supported either.
        if (defined('PASSWORD_ARGON2ID'))
            return password_hash($pass, PASSWORD_ARGON2ID);
        else if (defined('PASSWORD_ARGON2I'))
            return password_hash($pass, PASSWORD_ARGON2I);
        else
            return password_hash($pass, PASSWORD_BCRYPT);
    }

    /**
     * Handles POST requests on /signup
     *
     * @note Some fields in this request need to be set, namely:
     *       - `string email` the email of the newly created user account
     *       - `string password` the password for the new account, which must match some requirements:
     *         - must be 6 to 20 characters long
     *         - must contain at least one symbol in the SYMBOL_SET
     *         - must contain at least one digit in the DIGIT_SET
     *       - `string password_confirm` which must be the exact same as the `password` parameter
     *       - `string first_name` is the first name of the new user
     *       - `string last_name` is the last name of the new user
     *       - `bool sex` is whether the new user is a male (`true`) or a female (`false`)
     *       - `bool has_car` is whether the new user has a car that he can use to create trips
     *       - `string phone_number` is the phone number of the user
     *       And some fields are optional, namely:
     *       - `blob photo` which contains the profile picture of the user
     *
     * @param Request $req the PSR-7 Request object which originates from the HTTP request
     *
     * @param Response $resp
     *
     * @param array<mixed> $args
     *
     * @return Response
     * */
    public static function signup_post($req, $resp, $args) {
        $params = (array) $req->getParsedBody();
        $files = $req->getUploadedFiles();
        $mail = $params['email'] ?? '';
        $pass = $params['password'] ?? '';
        $pass_confirm = $params['password_confirm'] ?? '';
        $fname = $params['first_name'] ?? '';
        $lname = $params['last_name'] ?? '';
        $sex = (bool) $params['sex'] ?? true;
        $car = (bool) $params['has_car'] ?? false;
        $tel = $params['phone_number'] ?? '';


        $photo = $files['photo'] ?? null;
        try {
            if (empty($mail = htmlspecialchars($mail)))
                throw new \DomainException(json_encode(['err' => 'L\'e-mail est un champ qui doit être renseigné', 'field' => 'email']));
            if (!filter_var($mail, FILTER_VALIDATE_EMAIL))
                throw new \DomainException(json_encode(['err' => 'L\'e-mail indiqué n\'est pas valide (pas de la forme "abc@def.ghi")', 'field' => 'email']));
            $passlen = strlen($pass);
            if ($passlen < 6 || $passlen > 20)
                throw new \DomainException(json_encode(['err' => 'Le mot de passe indiqué est trop/pas assez long (doit être de longueur entre 6 et 20 caractères)', 'field' => 'password']));
            if (strcspn($pass, self::SYMBOL_SET) === $passlen || strcspn($pass, self::DIGIT_SET) === $passlen)
                // password does not contain at least one symbol
                                                                    // password does not contain at least one digit
                throw new \DomainException(json_encode(['err' => 'Le mot de passe doit contenir au moins un chiffre et un symbole', 'field' => 'password']));
            if ($pass !== $pass_confirm)
                throw new \DomainException(json_encode(['err' => 'La confirmation de mot de passe est différente du mot de passe', 'field' => 'password_confirm']));
            if (empty($fname = htmlspecialchars($fname)))
                throw new \DomainException(json_encode(['err' => 'Le prénom est un champ obligatoire', 'field' => 'first_name']));
            if (empty($lname = htmlspecialchars($lname)))
                throw new \DomainException(json_encode(['err' => 'Le nom est un champ qui doit être renseigné', 'field' => 'last_name']));
            if (empty($tel = htmlspecialchars($tel)))
                throw new \DomainException(json_encode(['err' => 'Le numéro de téléphone est requis afin d\'accéder à notre plateforme', 'field' => 'phone_number']));

            $pass = self::hash($pass);

            if (!is_null($photo) && $photo->getError() !== UPLOAD_ERR_NO_FILE && $photo->getError() !== UPLOAD_ERR_OK)
                throw new \DomainException(json_encode(['err' => 'Impossible de récupérer l\'image de profil', 'field' => 'photo']));

            $u = User::where([['email', '=', $mail],
                              ['deleted', '=', FALSE]])->first();
            // get the first user with this email which is NOT a deleted user
            if ($u)
                throw new \DomainException(json_encode(['err' => "L'utilisateur $mail existe déjà", 'field' => 'email']));

            // save a new user with the given credentials and various information
            $u = new User;
            $u->email = $mail;
            $u->pwd_hash = $pass;
            $u->nom = $lname;
            $u->prenom = $fname;
            $u->sexe = $sex;
            $u->voiture = $car;
            $u->tel = $tel;
            $u->photo = !is_null($photo) && is_uploaded_file($photo->getFilePath()) ? self::moveUploadedFile(__DIR__ . '/../../uploads', $photo) : NULL;
                        // check that a file has been uploaded before trying to save it to a local file
            if (!$u->save())
                throw new \InvalidArgumentException(json_encode([ 'err' => 'Impossible de créer un nouvel utilisateur. Veuillez réessayer plus tard.' ]));

            ConnectionManager::getInstance()->setIdConnected($u->idUser);
        } catch (\DomainException $e) {
            // We use exceptions here to stop processing parameters as long as one does not satisfy a given
            // predicate.
            //
            // The exception `$e` contains a valid JSON string that can be sent back to the application.

            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                        ->withStatus(400);
        } catch (\InvalidArgumentException $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')
                        ->withStatus(500);
        }

        $url=RedirectManager::getInstance()->getUrlRedirect();
        $resp->getBody()->write(VroumApp::getInstance()->urlFor($url["route"],$url["param"]));
        return  $resp;
    }

    /**
     * Handles POST requests on /login
     *
     * @note The only two fields required here are:
     *       - `string email` the email of the user trying to log in
     *       - `string password` the password he thinks is his
     *
     * @param Request $req
     *
     * @param Response $resp
     *
     * @param array<mixed> $args
     *
     * @return Response
     * */
    public static function login_post($req, $resp, $args) {
        $params = (array) $req->getParsedBody();

        $mail = $params['email'] ?? '';
        $pass = $params['password'] ?? '';

        try {
            if (empty($mail))
                throw new \DomainException(json_encode(['err' => 'Veuillez indiquer votre e-mail de connexion']));

            $u = User::where([['email', '=', $mail],
                              ['deleted', '=', FALSE]])->first();
            if (!$u)
                throw new \DomainException(json_encode(['err' => 'Identifiant ou mot de passe incorrect']));
            if (!password_verify($pass, $u['pwd_hash']))
                // `password_verify` is able to infer the hashing algorithm that was used by `self::hash` when registering
                throw new \DomainException(json_encode(['err' => 'Identifiant ou mot de passe incorrect']));

            ConnectionManager::getInstance()->setIdConnected($u->idUser);
        } catch (\DomainException $e) {
            // We use exceptions here to stop processing parameters as long as one does not satisfy a given
            // predicate.
            //
            // The exception `$e` contains a valid JSON string that can be sent back to the application.

            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $url=RedirectManager::getInstance()->getUrlRedirect();
        $resp->getBody()->write(VroumApp::getInstance()->urlFor($url["route"],$url["param"]));
        return  $resp;
    }

    /**
     * Handles POST requests on /logout
     *
     * Simply disconnects the current user, may there be one at the moment.
     *
     * @param Request $req
     *
     * @param Response $resp
     *
     * @param array<mixed> $args
     *
     * @return Response
     * */
    public static function disconnect_post($req, $resp, $args) {
        if (ConnectionManager::getInstance()->disconnect()) {
            // when the connection manager is able to disconnect the current user (this should never fail)
            // then we simply redirect the user onto the welcome page
            $url=RedirectManager::getInstance()->getUrlRedirect();
            $resp->getBody()->write(VroumApp::getInstance()->urlFor($url["route"],$url["param"]));
            return $resp;
        }

        $resp->getBody()->write(json_encode(['err' => 'Impossible de se déconnecter à cause d\'une erreur']));
        return $resp->withStatus(503);
    }

    /* Taken from https://www.slimframework.com/docs/v4/cookbook/uploading-files.html */
    /**
     * Moves the uploaded file to the upload directory and assigns it a unique name
     * to avoid overwriting an existing uploaded file.
     *
     * @param string $dir The directory to which the file is moved
     * @param UploadedFile $file The file uploaded file to move
     *
     * @return string The filename of moved file
     */
    private static function moveUploadedFile($dir, $file) {
        $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $file->moveTo($dir . DIRECTORY_SEPARATOR . $filename);

        return VroumApp::getInstance()->urlFor('uploads', [ 'file' => $filename ]);
    }

    public static function login($req, Response $resp, $args) {
        $iv = new IdentificationView();
        $resp->getBody()->write($iv->renderLogin());
        return $resp;
    }

    public static function signup($req, Response $resp, $args) {
        $iv = new IdentificationView();
        $resp->getBody()->write($iv->renderSignup());
        return $resp;
    }

    public static function profile($req, Response $resp, $args) {
        RedirectManager::getInstance()->refreshCookieUrlRedirect($req,$args);
        $id = ConnectionManager::getInstance()->getIdConnected();
        $u = User::where('idUser', '=', $id)->first();
        $iv = new IdentificationView();
        $resp->getBody()->write($iv->renderProfile($u));
        return $resp;
    }

    public static function resetPassword($req, Response $resp, $args){
        $iv = new IdentificationView();
        $resp->getBody()->write($iv->renderResetPassword($args['link']));
        return $resp;
    }

    public static function askToResetPassword($req, Response $resp, $args){
        $iv = new IdentificationView();
        $resp->getBody()->write($iv->renderAskToResetPassword());
        return $resp;
    }


    /**
     * Handles POST requests on /profile/modify
     *
     * Takes the same request parameters as `signup_post`.
     *
     * @param Request $req
     *
     * @param Response $resp
     *
     * @param array<mixed> $args
     *
     * @return Response
     * */
    public static function profile_post($req, $resp, $args) {
        $params = (array) $req->getParsedBody();

        $files = $req->getUploadedFiles();
        $id = ConnectionManager::getInstance()->getIdConnected();

        $user = User::where('idUser', '=', $id)->first();

        try {
            if (empty($first_name = htmlspecialchars($params['first_name'] ?? '')))
                throw new \DomainException(json_encode([ 'err' => 'Le prénom est un champ obligatoire', 'field' => 'first_name' ]));
            if (empty($last_name = htmlspecialchars($params['last_name'] ?? '')))
                throw new \DomainException(json_encode([ 'err' => 'Le nom est un champ requis du formulaire', 'field' => 'last_name' ]));
            if (empty($phone = htmlspecialchars($params['tel_num'] ?? '')))
                throw new \DomainException(json_encode([ 'err' => 'Le numéro de téléphone est requis afin d\'utiliser cette plateforme', 'field' => 'tel_num' ]));
            if (strlen($sex = htmlspecialchars($params['sex'] ?? '')) === 0)
                throw new \DomainException(json_encode([ 'err' => 'Veuillez indiquer votre sexe', 'field' => 'sex' ]));
            if (strlen($has_car = htmlspecialchars($params['car'] ?? '')) === 0)
                throw new \DomainException(json_encode([ 'err' => 'Vous n\'avez pas indiqué si vous possédez une voiture', 'field' => 'car' ]));
            if (!empty($pass = $params['password'] ?? '') && (strlen($pass) < 6 || strlen($pass) > 20))
                throw new \DomainException(json_encode([ 'err' => 'Le nouveau mot de passe entré est trop/pas assez long (il doit être de longueur entre 6 et 20 caractères)', 'field' => 'password' ]));
            $passlen = strlen($pass);
            if (!empty($pass) && (strcspn($pass, self::SYMBOL_SET) === $passlen || strcspn($pass, self::DIGIT_SET) === $passlen))
                throw new \DomainException(json_encode([ 'err' => 'Le nouveau mot de passe doit contenir au moins un chiffre et un symbole', 'field' => 'password' ]));
            if (!empty($pass) && $pass !== ($params['password_confirm'] ?? ''))
                throw new \DomainException(json_encode([ 'err' => 'Veuillez correctement confirmer votre mot de passe', 'field' => 'password_confirm' ]));

            $photo = $files['photo'] ?? NULL;
            if (!is_null($photo) && $photo->getError() !== UPLOAD_ERR_NO_FILE && $photo->getError() !== UPLOAD_ERR_OK)
                throw new \DomainException(json_encode([ 'err' => 'Impossible de télécharger la nouvelle image de profil', 'field' => 'photo' ]));

            $receive_emails = (bool) ($params['email-notification'] ?? false);

            $sex = (bool) $sex;
            $has_car = (bool) $has_car;

            // new fields set = any field has changed, in which case we update the new user
            $newFields = $user->prenom !== $first_name
                      || $user->nom !== $last_name
                      || $user->tel !== $phone
                      || $user->sexe != $sex
                      || $user->voiture !== $has_car
                      || !empty($pass)
                      || (!is_null($photo) && $photo->getError() === UPLOAD_ERR_OK)
                      || $receive_emails !== $user->recoit_email;

            if ($user->prenom !== $first_name)
                $user->prenom = $first_name;
            if ($user->nom !== $last_name)
                $user->nom = $last_name;
            if ($user->tel !== $phone)
                $user->phone = $phone;
            if ($user->sexe !== $sex)
                $user->sexe = $sex;
            if ($user->voiture !== $has_car)
                $user->voiture = $has_car;
            if ($user->recoit_email !== $receive_emails)
                $user->recoit_email = $receive_emails;

            if (!empty($pass)) {
                $pass = self::hash($pass);

                $user->pwd_hash = $pass;
            }

            if (!is_null($photo) && $photo->getError() === UPLOAD_ERR_OK) {
                $user->photo = self::moveUploadedFile(__DIR__ . '/../../uploads', $photo);
            }

            if ($newFields) { // update only if new fields have been inputted
                if (!$user->save()) {
                    $resp->getBody()->write(json_encode([ 'err' => 'Impossible de mettre à jour le compte à cause d\'une erreur interne' ]));
                    return $resp->withHeader('Content-Type', 'application/json')->withStatus(500);
                }
            }
        } catch (\DomainException $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $url=RedirectManager::getInstance()->getUrlRedirect();
        $resp->getBody()->write(VroumApp::getInstance()->urlFor($url["route"],$url["param"]));
        return  $resp;
    }

    /**
     * Handles POST requests on /profile/delete
     *
     * Simply marks the current 'User' as deleted and disconnects it.
     *
     * @param Request $req
     *
     * @param Response $resp
     *
     * @param array<mixed> $args
     *
     * @return Response
     * */
    public static function profileDelete_post($req, $resp, $args) {
        $id = ConnectionManager::getInstance()->getIdConnected();

        $u = User::where('idUser', '=', $id)->first();
        $u->deleted = TRUE;
        $u->save();

        return self::disconnect_post($req, $resp, $args);
    }

    public static function resetPassword_post($req, $resp, $args) {
        $params = (array) $req->getParsedBody();

        $mail = $params['mail'] ?? '';

        try {
            if (empty($mail) || !filter_var($mail, FILTER_VALIDATE_EMAIL))
                throw new \DomainException(json_encode([ 'err' => 'Adresse email invalide.', 'field' => 'mail' ]));

            $user = User::where('email', '=', $mail)->where('deleted', '=', 0)->first();
            if (!$user)
                throw new \DomainException(json_encode([ 'err' => 'Adresse email inconnue.', 'field' => 'mail' ]));

            $timestamp = $_SERVER['REQUEST_TIME'];
            $userId = $user['idUser'];

            $uniqueID = Crypt::getInstance()->encrypt("$userId:$timestamp");

            $va = VroumApp::getInstance();
            $resetUrl = $va->siteURL() . $va->urlFor('resetPassword', ['link' => $uniqueID]);

            $subject = 'Réinitialisation de votre mot de passe';
            $message =<<<END
<div style="position: relative; padding: 1.5rem; justify-content: space-around;align-items: center;min-height: 200px; text-align: center;">
    <div>
        Veuillez suivre 
        <a href="$resetUrl">ce lien</a>
        afin de réinitialiser votre mot de passe.
    </div>
    <div>
        Le lien est valide pendant 10 minutes. Après cela, il vous faudra en régénérer un nouveau.
        <hr>
        Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer ce message.
    </div>
</div>
END;

            $res = MailManager::getInstance()->sendFromNoReply($mail, $subject, $message);

            if ($res->statusCode() > 299)
                throw new \DomainException(json_encode([ 'err' => "Impossible d'envoyer le mail de réinitialisation de mot de passe : {$res->statusCode()} - {$res->body()}" ]));
        } catch (\DomainException $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withStatus(400)->withHeader('Content-Type', 'application/json');
        }


        $resp->getBody()->write(VroumApp::getInstance()->urlFor('askToResetPasswordDone', [ 'id' => $user->idUser ]));
        return $resp;
    }

    private const LINK_VALIDITY = 10 * 1000;
    //                           \__/ \____/
    //                            |     `- seconds to milliseconds
    //                            `- minutes to seconds

    public static function resetPasswordFromLink_post($req, $resp, $args) {
        $params = (array) $req->getParsedBody();

        $pass = $params['password'];
        $confirm = $params['password_confirm'];
        $link = $params['link'];
        list($userId, $timestamp) = explode(':', Crypt::getInstance()->decrypt($link));
        $now = $_SERVER['REQUEST_TIME'];

        try {
            if ($now > $timestamp + self::LINK_VALIDITY)
                throw new \DomainException(json_encode(['err' => 'Lien de réinitialisation de mot de passe invalide.']));

            $user = User::where([['idUser', '=', $userId], ['deleted', '=', 0]])->first();
            if (!$user)
                throw new \DomainException(json_encode(['err' => 'Utilisateur inconnu.']));


            $passlen = strlen($pass);
            if ($passlen < 6 || $passlen > 20)
                throw new \DomainException(json_encode(['err' => 'Le mot de passe indiqué est trop/pas assez long (doit être de longueur entre 6 et 20 caractères)', 'field' => 'password']));
            if (strcspn($pass, self::SYMBOL_SET) === $passlen || strcspn($pass, self::DIGIT_SET) === $passlen)
                // password does not contain at least one symbol
                                                                    // password does not contain at least one digit
                throw new \DomainException(json_encode(['err' => 'Le mot de passe doit contenir au moins un chiffre et un symbole', 'field' => 'password']));
            if ($pass !== $confirm)
                throw new \DomainException(json_encode(['err' => 'La confirmation de mot de passe est différente du mot de passe', 'field' => 'password_confirm']));

            $user->pwd_hash = self::hash($pass);
            $user->save();
        } catch (\DomainException $e) {
            $resp->getBody()->write($e->getMessage());
            return $resp->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $url=RedirectManager::getInstance()->getUrlRedirect();
        $resp->getBody()->write(VroumApp::getInstance()->urlFor($url["route"],$url["param"]));
        return  $resp;
    }


    public static function askToResetPasswordDone($req, $resp, $args) {
        $idUser = $args['id'];
        $user = User::find($idUser);

        $iv = new IdentificationView();
        $resp->getBody()->write($iv->renderAskToResetPasswordDone($user));
        return  $resp;
    }

}

?>
