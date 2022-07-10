<?php

namespace Vroum\Model;

use Illuminate\Database\Eloquent\Model;
use Vroum\Model\User;

use Vroum\Model\InterCity;


class Trip extends Model {
    protected $table = 'Trajet';
    protected $idTrajet = 'idTrajet';
    protected $dateD = 'dateD';
    protected $dateA = 'dateA';
    protected $conducteur = 'conducteur';
    protected $villeD = 'villeD';
    protected $villeA = 'villeA';
    protected $prix = 'prix';
    protected $cancelled = 'cancelled';
    protected $placeMax = 'placeMax';
    protected $precisionsRDV = 'precisionsRDV';
    protected $precisionsContraintes = 'precisionsContraintes';
    protected $listePrivee = 'listePrivee';
    public $timestamps = false;
    public $primaryKey = 'idTrajet';
    protected $hidden = ['pivot'];


    public function participants() {
        return $this->belongsToMany(User::class, 'participeATrajet', 'idTrajet', 'participant');
    }

    public function intermediateCities() {
        return $this->hasMany(InterCity::class, 'idTrip', 'idTrajet');
    }
}

?>
