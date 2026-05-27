<?php

namespace App\Observers;

use App\Models\Tag;
use Illuminate\Support\Facades\Cache;

class TagObserver
{
    /**
     * Handle the Tag "saved" event (created or updated).
     */
    public function saved(Tag $tag): void
    {
        $this->flushTagCaches($tag);
    }

    /**
     * Handle the Tag "deleted" event.
     */
    public function deleted(Tag $tag): void
    {
        $this->flushTagCaches($tag);
    }

    public function updated(Tag $tag): void
    {
        $this->flushTagCaches($tag);
    }

    /**
     * Remove all tag-related caches for the issuer associated with this tag.
     */
    private function flushTagCaches(Tag $tag): void
    {
        $issuerId = $tag->category?->issuer_id;

        if (! $issuerId) {
            return;
        }

        $cacheKeys = [
            "tags_used_in_upload_file_{$issuerId}",
            "tags_used_in_nfe_grouped_{$issuerId}",
            "tags_used_in_cte_grouped_{$issuerId}",
            "tags_used_in_nfse_grouped_{$issuerId}",
            "tags_used_in_nfe_{$issuerId}",
            "tags_used_in_cte_{$issuerId}",
            "category_tag_{$issuerId}_all",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}
