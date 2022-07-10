<?php

namespace Vroum\Model;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * Valeur par défaut de la notation d'un utilisateur
     */
    public const NOTATION_DEFAULT = -1;
    /**
     * Si l'utilisateur n'est "rien" dans un trajet (voir CONDUCTEUR_TRIP, CARPOOLER_TRIP et WAITING_ANSWER_PARTICIPATION_TRIP pour comprendre)
     */
    public const NOTHING_TRIP = 0;
    /**
     * Si l'utilisateur est conducteur dans un trajet
     */
    public const CONDUCTEUR_TRIP = 1;
    /**
     * Si l'utilisateur est covoitureur dans un trajet
     */
    public const CARPOOLER_TRIP = 2;
    /**
     * Si l'utilisateur attend une réponse à se demande de participation à un trajet
     */
    public const WAITING_ANSWER_PARTICIPATION_TRIP = 3;

    protected $table = 'User';
    protected $idUser = 'idUser';
    protected $email = 'email';
    protected $deleted = 'deleted';
    protected $pwd_hash = 'pwd_hash';
    protected $nom = 'nom';
    protected $prenom = 'prenom';
    protected $sexe = 'sexe';
    protected $voiture = 'voiture';
    protected $tel = 'tel';
    protected $photo = 'photo';
    protected $recoit_mails = 'recoit_email';
    public $timestamps = false;
    public $primaryKey = 'idUser';


    public function getMoyenneAttribute()
    {
        $res = self::NOTATION_DEFAULT;
        $scores = $this->hasMany(Note::class, 'note', 'idUser')->get();
        if (sizeof($scores)) {
            $score = 0;
            foreach ($scores as $n) {
                $score += $n->notation;
            }
            $res = $score / sizeof($scores);
        }
        return $res;
    }

    /**
     * Donne la query des trajets ou l'utilisateur est conducteur
     * @return mixed
     */
    public function trips_where_driver()
    {
        return $this->hasMany(Trip::class, 'conducteur', 'idUser');
    }

    /**
     * Donne la liste des idtrajets ou l'utilisateur est conducteur
     * @return array
     */
    private function id_trips_where_carpooler()
    {
        return
            array_column(
                array_column(
                    ParticipeATrajet::where('participant', $this->attributes['idUser'])->get()->all(),
                    'attributes'
                ),
                'idTrajet'
            );
    }

    /**
     * Donne la query des trajets ou l'utilisateur est passager
     * @return mixed
     */
    public function trips_where_carpooler()
    {
        return Trip::whereIn('idTrajet', $this->id_trips_where_carpooler());
    }

    /**
     * Donne la liste des idtrajets ou l'utilisateur a fait une demande de participation
     * @return array
     */
    private function id_trips_where_user_waiting_answer_participation()
    {
        return
            array_column(
                array_column(
                    Notification::where('expediteur', $this->attributes['idUser'])->where('type', Notification::TYPE_ASK_PARTICIPATION_TRIP)->get()->all(),
                    'attributes'
                ),
                'trajet'
            );
    }

    /**
     * Donne la query des trajets ou l'utilisateur a fait une demande de participation
     * @return mixed
     */
    public function trips_where_user_waiting_answer_participation()
    {
        return Trip::whereIn('idTrajet', $this->id_trips_where_user_waiting_answer_participation());
    }


    /**
     * Donne l'état de l'utilisateur par rapport au trajet
     * @param $idTrip
     * @return int
     */
    public function howIsUserInTrip($idTrip)
    {
        $t = Trip::find($idTrip);
        if ($t != null) {
            if ($t->conducteur == $this->attributes['idUser']) {
                return self::CONDUCTEUR_TRIP;
            } else {
                $pAT = ParticipeATrajet::where("participant", '=', $this->attributes['idUser'])->where('idTrajet', '=', $t->idTrajet)->first();
                if ($pAT != null) {
                    return self::CARPOOLER_TRIP;
                } else {
                    $notifDemande = Notification::where('trajet', '=', $idTrip)
                        ->where('destinataire', '=', $t->conducteur)
                        ->where('expediteur', '=', $this->attributes['idUser'])
                        ->where('type', '=', Notification::TYPE_ASK_PARTICIPATION_TRIP)->first();
                    if ($notifDemande != null) {
                        return self::WAITING_ANSWER_PARTICIPATION_TRIP;
                    } else {
                        return self::NOTHING_TRIP;
                    }
                }
            }
        }
        return -1;
    }

    /**
     * Donne la query des notifications ou l'utilisateur est le destinataire
     * @return mixed
     */
    public function notificationsSent()
    {
        return Notification::where('destinataire', $this->attributes['idUser']);
    }
}

?>
