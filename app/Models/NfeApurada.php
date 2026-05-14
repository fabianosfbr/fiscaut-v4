<?php

namespace App\Models;

use AlizHarb\ActivityLog\Contracts\HasActivityLogTitle;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class NfeApurada extends Model implements HasActivityLogTitle
{
    use LogsActivity;

    protected $table = 'nfe_apuradas';

    protected $guarded = ['id'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function nfe()
    {
        return $this->belongsTo(NotaFiscalEletronica::class, 'nfe_id');
    }

    public function issuer()
    {
        return $this->belongsTo(Issuer::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(function () {
                $status = $this->status ? 'Apurada' : 'Não Apurada';

                return "Status da NFe: {$status}";
            });
    }

    public function getActivityLogTitle(): string
    {
        return "NFe: {$this->nfe->chave}";
    }
}
