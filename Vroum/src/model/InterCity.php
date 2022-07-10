<?php

namespace Vroum\Model;

use Illuminate\Database\Eloquent\Model;
use Vroum\Model\User;

class InterCity extends Model {
    protected $table = 'InterCity';
    protected $idTrip = 'idTrip';
    protected $city = 'city';
    public $timestamps = false;
}

?>
