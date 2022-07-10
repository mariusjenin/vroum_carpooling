<?php

namespace Vroum\Model;

use Illuminate\Database\Eloquent\Model;

class ParticipeATrajet extends Model {
    protected $table = 'participeATrajet';
    protected $participant = 'participant';
    protected $idTrajet = 'idTrajet';

    // Eloquent doesn't support composite primary keys
//    public $primaryKey = ['participant', 'idTrajet'];
    public $timestamps = false;
}

?>
