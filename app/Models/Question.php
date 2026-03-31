<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class)->orderBy('order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Retorna todas as questões ativas com opções de forma resiliente ao cache.
     */
    public static function getActiveCached()
    {
        $questions = \Illuminate\Support\Facades\Cache::remember('questions.active.options', 86400, function () {
            return static::active()
                ->ordered()
                ->with('options')
                ->get();
        });

        // Proteção contra erro de desserialização (incomplete object)
        if (!$questions instanceof \Illuminate\Support\Collection) {
            \Illuminate\Support\Facades\Cache::forget('questions.active.options');
            return static::active()
                ->ordered()
                ->with('options')
                ->get();
        }

        return $questions;
    }

    /**
     * Calcula a pontuação máxima possível somando o maior peso de cada questão ativa.
     */
    public static function getMaxPossibleScore(): int
    {
        return static::getActiveCached()->sum(function ($question) {
            return $question->options->max('weight') ?? 5;
        });
    }
}
