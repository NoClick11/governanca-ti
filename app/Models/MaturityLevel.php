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

    /**
     * Limpa o cache sempre que o banco de dados é alterado.
     */
    protected static function booted()
    {
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('maturity_levels.ordered'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('maturity_levels.ordered'));
    }

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

        return static::getAllOrdered()->first(function ($level) use ($percentage) {
            return $percentage >= $level->min_score && $percentage <= $level->max_score;
        });
    }

    /**
     * Retorna todos os níveis ordenados de forma resiliente ao cache.
     */
    public static function getAllOrdered()
    {
        $levels = \Illuminate\Support\Facades\Cache::remember('maturity_levels.ordered', 86400, function () {
            return static::orderBy('order')->get();
        });

        // Proteção contra erro de desserialização (incomplete object / __Incomplete_Class)
        if (!$levels instanceof \Illuminate\Support\Collection) {
            \Illuminate\Support\Facades\Cache::forget('maturity_levels.ordered');
            return static::orderBy('order')->get();
        }

        return $levels;
    }
}
