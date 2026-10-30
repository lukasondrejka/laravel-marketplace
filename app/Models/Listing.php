<?php

namespace App\Models;

use App\Enums\ListingCondition;
use App\Enums\ListingStatus;
use App\Enums\ListingType;
use Database\Factories\ListingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property ListingStatus $status
 * @property ListingType $type
 * @property ListingCondition $condition
 */
#[Fillable(['category_id', 'type', 'title', 'description', 'price', 'currency', 'condition', 'status', 'city', 'show_phone', 'published_at'])]
class Listing extends Model implements HasMedia
{
    /** @use HasFactory<ListingFactory> */
    use HasFactory, InteractsWithMedia, SoftDeletes;

    public const IMAGES = 'images';

    public const MAX_IMAGES = 8;

    /** Slugs that would collide with static /listings/* routes. */
    public const RESERVED_SLUGS = ['create'];

    protected static function booted(): void
    {
        static::creating(function (Listing $listing) {
            $listing->slug ??= static::uniqueSlug($listing->title);
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = rtrim(Str::limit(Str::slug($title) ?: 'listing', 80, ''), '-');
        $slug = $base;

        while (in_array($slug, self::RESERVED_SLUGS, true) || static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(5));
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'type' => ListingType::class,
            'show_phone' => 'boolean',
            'condition' => ListingCondition::class,
            'status' => ListingStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::IMAGES)
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->performOnCollections(self::IMAGES)
            ->fit(Fit::Crop, 480, 360)
            ->format('webp');

        $this->addMediaConversion('large')
            ->performOnCollections(self::IMAGES)
            ->fit(Fit::Max, 1400, 1400)
            ->format('webp');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Cover image: the first image by order.
     */
    public function coverMedia(): ?Media
    {
        return $this->getMedia(self::IMAGES)->first();
    }

    public function coverUrl(string $conversion = 'thumb'): ?string
    {
        $media = $this->coverMedia();

        return $media ? $media->getUrl($conversion) : null;
    }

    /**
     * Contact phone shown on the listing: the seller's profile number, only if they opted in.
     */
    public function contactPhone(): ?string
    {
        return $this->show_phone ? ($this->user->phone ?: null) : null;
    }

    public function isOwnedBy(?User $user): bool
    {
        return $user !== null && $user->getKey() === $this->user_id;
    }

    public function formattedPrice(): string
    {
        return Number::currency((float) $this->price, in: $this->currency, locale: app()->getLocale()) ?: (string) $this->price;
    }

    /**
     * @return Attribute<HtmlString, never>
     */
    protected function richDescription(): Attribute
    {
        return Attribute::get(function () {
            $escaped = e($this->description);
            $linked = preg_replace_callback(
                '~https?://[^\s<]+[^\s<.,;:!?)\]]~i',
                fn ($m) => '<a href="'.$m[0].'" target="_blank" rel="nofollow noopener noreferrer ugc" class="link link-primary">'.$m[0].'</a>',
                $escaped
            );

            return new HtmlString(nl2br($linked));
        });
    }

    /**
     * @return Attribute<string, never>
     */
    protected function teaser(): Attribute
    {
        return Attribute::get(fn () => Str::limit(strip_tags($this->description), 140));
    }

    /**
     * Publicly visible listings.
     *
     * @param  Builder<Listing>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', ListingStatus::Active)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /**
     * @param  Builder<Listing>  $query
     */
    public function scopeOfType(Builder $query, ?ListingType $type): void
    {
        if ($type !== null) {
            $query->where('type', $type);
        }
    }

    /**
     * Multi-word AND search over title and description. Blank words are ignored; input is capped
     * (100 characters, 8 words) so a crafted query cannot build a huge SQL statement.
     *
     * Accent- and case-insensitivity ("kosice" finds "Košice") comes from the MySQL utf8mb4_unicode_ci
     * collation; SQLite's LIKE only folds ASCII case.
     *
     * @param  Builder<Listing>  $query
     */
    public function scopeSearch(Builder $query, ?string $keyword): void
    {
        $words = array_slice(preg_split('/[\s,]+/u', mb_substr(trim((string) $keyword), 0, 100), -1, PREG_SPLIT_NO_EMPTY), 0, 8);

        foreach ($words as $word) {
            // An explicit ESCAPE character: LIKE has no default escape in SQLite, unlike MySQL.
            $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word).'%';
            $query->where(fn ($q) => $q->whereRaw("title like ? escape '!'", [$like])->orWhereRaw("description like ? escape '!'", [$like]));
        }
    }

    /**
     * Bounds are inclusive; null means unbounded, 0 is a valid bound.
     *
     * @param  Builder<Listing>  $query
     */
    public function scopePriceBetween(Builder $query, int|float|string|null $min, int|float|string|null $max): void
    {
        if ($min !== null && $min !== '') {
            $query->where('price', '>=', $min);
        }

        if ($max !== null && $max !== '') {
            $query->where('price', '<=', $max);
        }
    }

    /**
     * @param  Builder<Listing>  $query
     */
    public function scopeSortBy(Builder $query, ?string $sort): void
    {
        match ($sort) {
            'price_asc' => $query->orderBy('price')->orderByDesc('id'),
            'price_desc' => $query->orderByDesc('price')->orderByDesc('id'),
            default => $query->orderByDesc('published_at')->orderByDesc('id'),
        };
    }
}
