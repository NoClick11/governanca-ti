<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaturityIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Retorna todos os indicadores de forma resiliente ao cache.
     */
    public static function getAllCached()
    {
        $indicators = \Illuminate\Support\Facades\Cache::remember('indicators.all', 86400, function () {
            return static::all();
        });

        // Proteção contra erro de desserialização (incomplete object)
        if (!$indicators instanceof \Illuminate\Support\Collection) {
            \Illuminate\Support\Facades\Cache::forget('indicators.all');
            return static::all();
        }

        return $indicators;
    }
}
