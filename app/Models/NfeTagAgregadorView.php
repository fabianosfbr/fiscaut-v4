<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeTagAgregadorView extends Model
{
    protected $table = 'nfe_tag_agregador_view';

    public $incrementing = false;

    protected $primaryKey = 'code';

    public $timestamps = false;
}
