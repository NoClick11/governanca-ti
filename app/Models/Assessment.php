<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'total_score',
        'max_score',
        'maturity_level',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_score' => 'integer',
            'max_score' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AssessmentAnswer::class);
    }

    /**
     * Calcula a pontuação total somando os pesos das alternativas selecionadas.
     */
    public function calculateScore(): int
    {
        return $this->answers()
            ->join('options', 'assessment_answers.option_id', '=', 'options.id')
            ->sum('options.weight');
    }

    /**
     * Determina o nível de maturidade com base na pontuação e nota máxima.
     */
    public function determineMaturityLevel(): ?MaturityLevel
    {
        $score = $this->total_score ?? $this->calculateScore();
        $maxScore = $this->max_score ?? Question::getMaxPossibleScore();
        
        return MaturityLevel::getForScore($score, $maxScore);
    }

    /**
     * Finaliza a avaliação, calculando score e determinando nível.
     */
    public function complete(): self
    {
        $this->total_score = $this->calculateScore();
        $this->max_score = Question::getMaxPossibleScore();
        
        $level = MaturityLevel::getForScore($this->total_score, $this->max_score);
        $this->maturity_level = $level?->name;
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();

        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Retorna o progresso da avaliação (percentual de questões respondidas).
     */
    public function getProgress(): array
    {
        $totalQuestions = Question::active()->count();
        $answeredQuestions = $this->answers()->count();

        return [
            'total' => $totalQuestions,
            'answered' => $answeredQuestions,
            'percentage' => $totalQuestions > 0
                ? round(($answeredQuestions / $totalQuestions) * 100)
                : 0,
        ];
    }
}
