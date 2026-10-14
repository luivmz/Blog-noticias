<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = ['category_id', 'title', 'summary', 'content', 'status'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if (! $post->exists || $post->isDirty('title')) {
                $base = Str::slug($post->title) ?: 'noticia';
                $slug = $base;
                $suffix = 2;
                while (static::where('slug', $slug)->when($post->exists, fn ($q) => $q->where('id', '!=', $post->id))->exists()) {
                    $slug = $base.'-'.$suffix++;
                }
                $post->slug = $slug;
            }
            if ($post->status === 'published' && $post->published_at === null) {
                $post->published_at = now();
            }
        });
    }
}
