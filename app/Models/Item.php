<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'price',
        'category_id'

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
     *
     * @return Attribute
     */
    protected function teaser(): Attribute
    {
        $length = 100;
        $value = strlen($this->description) > $length
            ? substr($this->description, 0, $length) . '…'
            : $this->description;

        return new Attribute(get: fn () => $value);
    }

    /**
     * Rich text description.
     *
     * @return Attribute
     */
    public function richTextDescription(): Attribute
    {
        $value =  nl2br(preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@',
                '<a target="_blank" href="$1">$1</a> ',
                e($this->description) . " ")
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
