<?php

namespace Vroum\Model;

use Illuminate\Database\Eloquent\Model;

class Note extends Model {
    protected $table = 'Note';
    protected $idNote = 'idNote';
    protected $note = 'note';
    protected $notant = 'notant';
    protected $idTrajet = 'idTrajet';
    protected $notation = 'notation';
    public $primaryKey = 'idNote';
    public $timestamps = false;
}

?>
