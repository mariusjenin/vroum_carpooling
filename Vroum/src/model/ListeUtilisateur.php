<?php

namespace Vroum\Model;

use Illuminate\Database\Eloquent\Model;

class ListeUtilisateur extends Model {
    protected $table = 'ListeUtilisateur';
    protected $nom = 'nom';
    protected $createur = 'createur';

    public $primaryKey = 'idListe';
    public $timestamps = false;

    protected $fillable = ['nom', 'createur'];

    public function inscrits() {
        return $this->belongsToMany(User::class, 'appartientAListe', 'idListe', 'idUser');
    }

    public function trajetsConcernes() {
        return $this->hasMany(Trip::class, 'listePrivee', 'idListe');
    }
}

?>
