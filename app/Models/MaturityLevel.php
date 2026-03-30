<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaturityLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_score',
        'max_score',
        'description',
        'improvements',
        'color',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'min_score' => 'integer',
            'max_score' => 'integer',
            'order' => 'integer',
        ];
    }

    /**
     * Retorna o nível de maturidade correspondente à pontuação.
     */
    public static function getForScore(int $score, int $maxScore): ?self
    {
        if ($maxScore <= 0) return null;
        
        $percentage = ($score / $maxScore) * 100;

        return static::where('min_score', '<=', $percentage)
            ->where('max_score', '>=', $percentage)
            ->first();
    }

    /**
     * Retorna todos os níveis ordenados.
     */
    public static function getAllOrdered()
    {
        return static::orderBy('order')->get();
    }
}
