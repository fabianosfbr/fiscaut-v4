<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogSefazManifestoEvent extends Model
{
    use HasFactory;

    public $table = 'log_sefaz_manifesto_event';

    protected $guarded = ['id'];

    public function issuer()
    {
        return $this->belongsTo(Issuer::class);
    }
}
