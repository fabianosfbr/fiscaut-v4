<?php

namespace App\Models;

use App\Enums\DocTypeEnum;
use App\Models\Traits\HasTags;
use Illuminate\Database\Eloquent\Model;

class UploadFile extends Model
{
    use HasTags;
    
    protected $guarded = ['id'];

    protected $casts = [
        'doc_type' => DocTypeEnum::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function issuer()
    {
        return $this->belongsTo(Issuer::class);
    }
}
