<?php

namespace App\Models;

use App\Enums\XmlImportJobType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class XmlImportJob extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'errors' => 'array',
        'import_type' => XmlImportJobType::class,
    ];

    // Status constants
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    /**
     * Get the owner of the import job (polymorphic relationship).
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns the import job.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tenant that owns the import job.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the issuer that owns the import job.
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Issuer::class);
    }

    /**
     * Scope a query to only include jobs of a given import type.
     */
    public function scopeOfType($query, XmlImportJobType $type)
    {
        return $query->where('import_type', $type->value);
    }

    /**
     * Set the owner of the import job.
     */
    public function setOwner(Model $owner): self
    {
        $this->owner_id = $owner->getKey();
        $this->owner_type = get_class($owner);

        return $this;
    }

    /**
     * Check if the job is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the job is processing.
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if the job is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the job has failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if the job was created by a user.
     */
    public function isUserImport(): bool
    {
        return $this->import_type === XmlImportJobType::USER;
    }

    /**
     * Check if the job was created by the system.
     */
    public function isSystemImport(): bool
    {
        return $this->import_type === XmlImportJobType::SYSTEM;
    }

    /**
     * Get the progress percentage.
     */
    public function getProgressPercentage(): int
    {
        if ($this->total_files === 0) {
            return 0;
        }

        return (int) (($this->processed_files / $this->total_files) * 100);
    }

    /**
     * Add an error to the job.
     */
    public function addError(string $error): self
    {
        $errors = $this->errors ?? [];
        $errors[] = $this->sanitizeForJson($error);
        $this->errors = $errors;
        $this->error_files = ($this->error_files ?? 0) + 1;
        $this->save();

        return $this;
    }

    private function sanitizeForJson(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        if (preg_match('//u', $value) === 1) {
            return $value;
        }

        $iconv = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
        if (is_string($iconv) && $iconv !== '' && preg_match('//u', $iconv) === 1) {
            return $iconv;
        }

        if (function_exists('mb_convert_encoding')) {
            $mb = @mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            if (is_string($mb) && $mb !== '' && preg_match('//u', $mb) === 1) {
                return $mb;
            }
        }

        return 'base64:' . base64_encode($value);
    }

    /**
     * Increment the processed files count.
     */
    public function incrementProcessedFiles(): self
    {
        $this->processed_files++;
        $this->save();

        return $this;
    }

    public function incrementTotalFiles(): self
    {
        $this->total_files++;
        $this->save();

        return $this;
    }

    public function incrementNumDocuments(): self
    {
        $this->num_documents++;
        $this->save();

        return $this;
    }

    public function incrementNumEvents(): self
    {
        $this->num_events++;
        $this->save();

        return $this;
    }

    /**
     * Increment the imported files count.
     */
    public function incrementImportedFiles(): self
    {
        $this->imported_files++;
        $this->save();

        return $this;
    }
}
