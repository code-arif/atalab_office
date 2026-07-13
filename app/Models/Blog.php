<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
        'status' => 'string',
    ];

    /**
     * Boot the model and register model events.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Blog $blog) {
            if (empty($blog->slug)) {
                $blog->slug = $blog->generateUniqueSlug($blog->title);
            }
            // Auto-set published_at when status changes to published
            if ($blog->status === 'published' && empty($blog->published_at)) {
                $blog->published_at = now();
            }
            // Auto-set meta_title from title if not provided
            if (empty($blog->meta_title)) {
                $blog->meta_title = $blog->title;
            }
        });

        static::updating(function (Blog $blog) {
            // Update published_at when transitioning to published
            if ($blog->isDirty('status') && $blog->status === 'published' && empty($blog->published_at)) {
                $blog->published_at = now();
            }
            // Set meta_title from title if not provided
            if (empty($blog->meta_title)) {
                $blog->meta_title = $blog->title;
            }
        });
    }

    /**
     * Generate a unique URL-friendly slug from the given title.
     */
    public function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    /**
     * Scope a query to only include published blogs.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include draft blogs.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Get the author of the blog post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    /**
     * Accessor for featured image URL.
     */
    public function getFeaturedImageAttribute($value): string|null
    {
        if (empty($value)) {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        if (request()->is('api/*')) {
            return url($value);
        }

        return $value;
    }

    /**
     * Get the excerpt. Auto-generate from content if not explicitly set.
     */
    public function getExcerptAttribute($value): string|null
    {
        if (!empty($value)) {
            return $value;
        }

        // Auto-generate from content (strip tags, limit to 200 chars)
        $clean = strip_tags($this->content ?? '');
        return Str::limit($clean, 200);
    }

    /**
     * Get the reading time in minutes.
     */
    public function getReadingTimeAttribute(): int
    {
        $words = str_word_count(strip_tags($this->content ?? ''));
        return max(1, (int) ceil($words / 200));
    }
}
