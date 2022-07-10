<?php

namespace Vroum\Model;

use Illuminate\Database\Eloquent\Model;
use Vroum\Controler\IdentificationController;
use Vroum\Controler\MailManager;
use Vroum\View\IdentificationView;
use Vroum\VroumApp;

class Notification extends Model {
    protected $table = 'Notification';
    protected $idNote = 'idNotif';
    protected $destinataire = 'destinataire';
    protected $expediteur = 'expediteur';
    protected $texte = 'texte';
    protected $trajet = 'trajet';
    protected $lue = 'lue';
    protected $type = 'type';
    protected $date = 'date';

    public $primaryKey = 'idNotif';
    public $timestamps = false;

    public const TYPE_ASK_PARTICIPATION_TRIP = 'ASK_PARTICIPATION_TRIP';
    public const TYPE_DELETE_TRIP_OFFER = 'DELETE_TRIP_OFFER';
    public const TYPE_ACCEPT_PARTICIPATION_TRIP = 'ACCEPT_PARTICIPATION_TRIP';
    public const TYPE_REFUSE_PARTICIPATION_TRIP = 'REFUSE_PARTICIPATION_TRIP';
    public const TYPE_DELETE_PARTICIPATION_TRIP = 'DELETE_PARTICIPATION_TRIP';
    public const TYPE_CREATE_PRIVATE_USER_LIST = 'CREATE_PRIVATE_USER_LIST';


    public function getTitleAttribute(){
        $trajet = Trip::find($this->attributes['trajet']);
        switch ($this->attributes['type']){
            case self::TYPE_ASK_PARTICIPATION_TRIP:
                $res = "Demande de participation à votre trajet allant de $trajet->villeD à $trajet->villeA";
                break;
            case self::TYPE_DELETE_TRIP_OFFER:
                $res = "Annulation du trajet allant de $trajet->villeD à $trajet->villeA";
                break;
            case self::TYPE_ACCEPT_PARTICIPATION_TRIP:
                $res = "Demande de participation au trajet allant de $trajet->villeD à $trajet->villeA acceptée";
                break;
            case self::TYPE_REFUSE_PARTICIPATION_TRIP:
                $res = "Demande de participation au trajet allant de $trajet->villeD à $trajet->villeA refusée";
                break;
            case self::TYPE_DELETE_PARTICIPATION_TRIP:
                $res = "Annulation de la participation d'un covoitureur dans le trajet allant de $trajet->villeD à $trajet->villeA";
                break;
            case self::TYPE_CREATE_PRIVATE_USER_LIST:
                $res = "Un trajet privé allant de $trajet->villeD à $trajet->villeA vous concernant a été mis en ligne";
                break;
            default:
                $res = "Erreur lors du chargement du titre de la notification";
        }
        return $res;
    }

    private function getContentAttribute(){
        $userExp = User::find($this->attributes['expediteur']);
        $userDest = User::find($this->attributes['destinataire']);
        $trajet = Trip::find($this->attributes['trajet']);


        $text = $this->attributes['texte'];
        $date = $this->attributes['date']??date('Y-m-d H:i:s');
        $date_notif = $new_format = date('d/m/Y', strtotime($date));
        $hour_notif = $new_format = date('H:i', strtotime($date));

        $vi = $trajet->intermediateCities()->get()->all();

        if(count($vi)==0){
            $vi_str = "";
        } else {
            $vi_str = "passant par ";
            if(count($vi)>0){
                for($i = 0 ; $i< count($vi) ; $i++){
                    $delimiter = $i < count($vi)-1?", ":" et ";
                    $vi_str .= $vi[$i]->city;
                    if($i < count($vi)){
                        $vi_str .= $delimiter;
                    }
                }
            }
        }

        $str_trajet = "trajet allant de $trajet->villeD à $trajet->villeA $vi_str partant à $hour_notif le $date_notif";

        $msg = $text>"" ? "
                <div style='border-radius: 15px;
                            background-color: #F8F9FA;
                            width: max-content;
                            align-self: center;
                            line-height: 1.7rem;
                            display: flex;
                            flex-direction: row;
                            padding: 1rem;
                            margin-top: 0.5rem;' 
                        class='message_in_notification'>
                    <div style='padding-right: 5px;
                                padding-left: 5px;
                                font-size: 1.7rem;' 
                            class='double_quote_message_notification'>
                    \"
                    </div>
                    $text
                    <div style='padding-right: 5px;
                                padding-left: 5px;
                                font-size: 1.7rem;' 
                            class='double_quote_message_notification'>
                    \"
                    </div>
                </div>
                ":"";

        switch ($this->attributes['type']){
            case self::TYPE_ASK_PARTICIPATION_TRIP:
                $res = "$userExp->prenom $userExp->nom voudrait participer à votre $str_trajet.$msg";
                break;
            case self::TYPE_DELETE_TRIP_OFFER:
                $res = "Annulation du $str_trajet auquel vous participiez";
                break;
            case self::TYPE_ACCEPT_PARTICIPATION_TRIP:
                $res = "$userDest->prenom $userDest->nom a accepté votre demande de participation au $str_trajet";
                break;
            case self::TYPE_REFUSE_PARTICIPATION_TRIP:
                $res = "$userDest->prenom $userDest->nom a refusé votre demande de participation au $str_trajet";
                break;
            case self::TYPE_DELETE_PARTICIPATION_TRIP:
                $res = "$userExp->prenom $userExp->nom a annulé sa participation au $str_trajet.$msg";
                break;
            case self::TYPE_CREATE_PRIVATE_USER_LIST:
                $res = "$userExp->prenom $userExp->nom a créé le $str_trajet";
                break;
            default:
                $res = "Erreur lors du chargement du contenu de la notification";
        }
        return $res;
    }

    public function getContentNotifAttribute(){
        $userExp = User::find($this->attributes['expediteur']);
        $trajet = Trip::find($this->attributes['trajet']);

        $res = $this->getContentAttribute();
        if($this->attributes['type'] == self::TYPE_ASK_PARTICIPATION_TRIP){
            $url = VroumApp::getInstance()->urlFor("participateToTrip_post");
            $btn_choices = <<<END
                <div class="mt-3 d-flex flex-row justify-content-around align-items-center notif_box_choice">
                    <div onclick="post_button('{$url}',{idTrip:$trajet->idTrajet,idParticipant:$userExp->idUser,accept:0})" class="notif_choice btn_hoverable px-3 py-1">
                        Refuser
                    </div>
                    <div onclick="post_button('{$url}',{idTrip:$trajet->idTrajet,idParticipant:$userExp->idUser,accept:1})" class="notif_choice btn_hoverable px-3 py-1">
                        Accepter
                    </div>
                </div>
END;
            $res.= $btn_choices;
        }
        return $res;
    }

    public function getContentMailAttribute(){
        $content = $this->getContentAttribute();
        $url = VroumApp::getInstance()->siteURL().VroumApp::getInstance()->urlFor('allNotificationsFromUser', ["id" => $this->attributes['expediteur']]);
        $res=<<<END
            <div style="position: relative;
                    display: flex;
                    flex-direction: column; 
                    padding: 1.5rem;
                    justify-content: center;
                    align-items: center;
                    min-height: 200px;
                    text-align: center;
                    ">
                $content
                <div>
                    Vous pouvez retrouver cette notification à <a style=" " href="$url">ce lien</a>.
                </div>
            </div>
            
END;
        return $res;
    }

    public function getDeletableAttribute(){
        return $this->attributes['type'] != self::TYPE_ASK_PARTICIPATION_TRIP;
    }

    public function sendNotifByMail(){
        $userDest = User::find($this->attributes['destinataire']);

        $mail = $userDest->email;

        $subject = $this->getTitleAttribute();
        $msg = $this->getContentMailAttribute();

        return MailManager::getInstance()->sendFromNoReply($mail, $subject, $msg);
    }
}

?>
