<?php

namespace App\Models\Traits;

use App\Models\Tag;
use App\Models\Tagged;

trait HasTags
{
    public function tagged()
    {
        return $this->morphMany(Tagged::class, 'taggable')->with('tag');
    }

    public function untag()
    {
        $tags = $this->getTagsAttribute();
        foreach ($tags as $tag) {
            $this->tagged()->where('tag_id', $tag->id)->delete();
        }
    }

    public function tag($tag, $value, $product = null)
    {
        if ($tag instanceof Tag) {
            $tag = $tag;
        } else {
            $tag = Tag::find($tag);
        }
        $tagged = new Tagged([
            'tag_id' => $tag->id,
            'tag_name' => $tag->name,
            'tag_slug' => $tag->slug,
            'value' => $value,
            'product' => $product,
            'tenant_id' => $tag->tenant_id,
        ]);

        $this->tagged()->save($tagged);
    }



    public function getTagsAttribute()
    {
        return $this->tagged->map(function (Tagged $item) {
            return $item->tag;
        });
    }

    public function getTagNamesAttribute(): array
    {
        return $this->tagNames();
    }

    public function tagNames(): array
    {
        return $this->tagged->map(function ($item) {
            return $item->tag_name;
        })->toArray();
    }

    public function tagNamesWithCode(): array
    {
        return $this->tagged->map(function ($item) {
            return $item->tag?->code . ' - ' . $item?->tag_name;
        })->toArray();
    }

    public function tagNamesWithCodeAndValue(): array
    {
        return $this->tagged->map(function ($item) {
            return $item->tag->code . ' - ' . $item->tag_name . ' | R$' . number_format($item->value, 2, ',', '.');
        })->toArray();
    }

    public function tagAtrributes(): array
    {
        return $this->tagged->map(function ($item) {
            return $item->tag->code . ' - ' . $item->tag_name . ' | ' . $item->value . ' | ' . $item->products;
        })->toArray();
    }

    public function tagSlugs(): array
    {
        return $this->tagged->map(function ($item) {
            return $item->tag_slug;
        })->toArray();
    }
}
