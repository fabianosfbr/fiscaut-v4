<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfseTagAgregadorView extends Model
{
    protected $table = 'nfse_tomada_tag_agregador_view';

    public $incrementing = false;

    protected $primaryKey = 'code';

    public $timestamps = false;
}
