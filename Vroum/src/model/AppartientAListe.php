<?php

namespace Vroum\Model;

use Illuminate\Database\Eloquent\Model;

class AppartientAListe extends Model {
    protected $table = 'appartientAListe';
    protected $idListe = 'idListe';
    protected $idUser = 'idUser';

    // FIXME: does Eloquent support multiple primary keys???
//    public $primaryKey = ['idListe', 'idUser'];
    public $timestamps = false;

    protected $fillable = ['idListe', 'idUser'];
}

?>
