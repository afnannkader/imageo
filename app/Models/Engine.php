<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Engine extends Model
{
    use HasFactory;

    const STATUS_DISABLED = 0;
    const STATUS_ACTIVE = 1;

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    public function supportNegativePrompt()
    {
        return $this->support_negative_prompt;
    }

    protected $fillable = [
        'name',
        'credentials',
        'filters',
        'sizes',
        'art_styles',
        'lightning_styles',
        'moods',
        'status',
    ];

    protected $casts = [
        'credentials' => 'object',
    ];

    public function getFiltersArray()
    {
        return explode(',', $this->filters);
    }

    public function getSizesArray()
    {
        return explode(',', $this->sizes);
    }

    public function getArtStylesArray()
    {
        return explode(',', $this->art_styles);
    }

    public function getLightningStylesArray()
    {
        return explode(',', $this->lightning_styles);
    }

    public function getMoodsArray()
    {
        return explode(',', $this->moods);
    }
}
