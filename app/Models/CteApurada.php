<?php

namespace App\Models;

use AlizHarb\ActivityLog\Contracts\HasActivityLogTitle;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class CteApurada extends Model implements HasActivityLogTitle
{
    use LogsActivity;

    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(function () {
                $status = $this->status ? 'Apurada' : 'Não Apurada';

                return "Status da CTe: {$status}";
            });
    }

    public function getActivityLogTitle(): string
    {
        return "CTe: {$this->cte->chave}";
    }

    public function cte()
    {
        return $this->belongsTo(ConhecimentoTransporteEletronico::class, 'cte_id');
    }
}
