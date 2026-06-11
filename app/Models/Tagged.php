<?php

namespace App\Models;

use AlizHarb\ActivityLog\Contracts\HasActivityLogTitle;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Tagged extends Model implements HasActivityLogTitle
{
    use LogsActivity;

    protected $table = 'tagging_tagged';

    public $timestamps = false;

    protected $cachePrefix = 'tagging_tagged';

    protected $guarded = ['id'];

    protected $casts = [
        'product' => 'json',
    ];

    public function taggable()
    {
        return $this->morphTo();
    }

    public function tag()
    {

        return $this->belongsTo(Tag::class, 'tag_id', 'id');
    }

    public function tagNamesWithCode(): array
    {
        return $this->tagged->map(function ($item) {
            return $item->tag->code.' - '.$item->tag_name;
        })->toArray();
    }

    public function getActivitylogOptions(): LogOptions
    {
        $this->loadMissing('taggable');

        $taggable = $this->taggable;

        $label = match (true) {
            $taggable instanceof NotaFiscalEletronica => "NFe {$taggable->chave}",
            $taggable instanceof ConhecimentoTransporteEletronico => "CTe {$taggable->chave}",
            $taggable instanceof NotaFiscalServico => "NFSe {$taggable->chave}",
            $taggable instanceof UploadFile => "Arquivo {$taggable->title}",
            default => class_basename($this->taggable_type).' #'.$this->taggable_id,
        };

        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(function () use ($label) {
                return "Etiqueta: {$this->tag->code} -  {$this->tag_name} em {$label}";
            });
    }

    public function getActivityLogTitle(): string
    {

        $taggable = $this->taggable;

        $textToReturn = $taggable ? 'Etiqueta: Aplicada' : 'Etiqueta: Removida';

        return $textToReturn;
    }
}
