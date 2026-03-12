<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuerDocument extends Model
{
    protected $guarded = ['id'];

    protected $appends = ['file_url'];

    protected $casts = [
        'file_size' => 'integer',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
        'validate' => 'date',
    ];

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Issuer::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getFileUrlAttribute()
    {
        return $this->file_path ? storage_path('app/private/'.$this->file_path) : null;
    }

    public function getFormattedFileSizeAttribute()
    {
        $size = $this->file_size;

        if ($size >= 1048576) {
            return number_format($size / 1048576, 2).' MB';
        } elseif ($size >= 1024) {
            return number_format($size / 1024, 2).' KB';
        } else {
            return $size.' bytes';
        }
    }
}
