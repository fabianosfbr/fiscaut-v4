<?php

namespace App\Models;

use AlizHarb\ActivityLog\Contracts\HasActivityLogTitle;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class NfseApurada extends Model implements HasActivityLogTitle
{
    use LogsActivity;

    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(function () {
                $status = $this->status ? 'Apurada' : 'Não Apurada';

                return "Status da NFSe: {$status}";
            });
    }

    public function getActivityLogTitle(): string
    {
        return "NFSe: {$this->nfse->chave}";
    }

    public function nfse()
    {
        return $this->belongsTo(NotaFiscalServico::class, 'nfse_id');
    }
}
