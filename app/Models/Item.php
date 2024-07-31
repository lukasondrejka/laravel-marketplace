<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    /**
     * Maximum length of the teaser.
     */
    private const TEASER_LENGTH = 400;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'price',
        'category_id',

    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'teaser',
        'rich_text_description',
    ];

    /**
     * Teaser of the description.
     */
    protected function teaser(): Attribute
    {
        $value = strlen($this->description) > self::TEASER_LENGTH
            ? substr($this->description, 0, self::TEASER_LENGTH).'…'
            : $this->description;

        return new Attribute(get: fn () => $value);
    }

    /**
     * Rich text description.
     */
    public function richTextDescription(): Attribute
    {
        $value = nl2br(preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@',
            '<a target="_blank" href="$1">$1</a> ',
            e($this->description).' ')
        );

        return new Attribute(get: fn () => $value);
    }

    /**
     * Get the user that owns the item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category of the item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
