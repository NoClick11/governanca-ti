<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Company;
use App\Models\MaturityLevel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Base query filtrada por empresa (multi-tenant)
        $assessmentsQuery = Assessment::query();
        $companiesQuery = Company::query();

        if (!$user->isAdminGlobal()) {
            $assessmentsQuery->where('company_id', $user->company_id);
        }

        // Métricas
        $totalAssessments = (clone $assessmentsQuery)->count();
        $completedAssessments = (clone $assessmentsQuery)->where('status', 'completed')->count();
        $averageScore = (clone $assessmentsQuery)->where('status', 'completed')->avg('total_score') ?? 0;
        $totalCompanies = $user->isAdminGlobal() ? Company::count() : 1;

        // Distribuição Nível de Otimização: Coleta apenas a coluna de score de uma vez e gera as distribuições por Coleção PHP (0ms contra 750ms do banco)
        $levels = MaturityLevel::getAllOrdered();
        $completedScores = (clone $assessmentsQuery)->where('status', 'completed')->pluck('total_score');
        $levelDistribution = [];

        foreach ($levels as $level) {
            $count = $completedScores->filter(function ($score) use ($level) {
                return $score >= $level->min_score && $score <= $level->max_score;
            })->count();

            $levelDistribution[] = [
                'name' => $level->name,
                'count' => $count,
                'color' => $level->color,
            ];
        }

        // Avaliações recentes
        $recentAssessments = (clone $assessmentsQuery)
            ->with(['company', 'user'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'company_name' => $a->company->name,
                'user_name' => $a->user->name,
                'status' => $a->status,
                'total_score' => $a->total_score,
                'maturity_level' => $a->maturity_level,
                'created_at' => $a->created_at->format('d/m/Y H:i'),
                'completed_at' => $a->completed_at?->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalAssessments' => $totalAssessments,
                'completedAssessments' => $completedAssessments,
                'averageScore' => round($averageScore, 1),
                'totalCompanies' => $totalCompanies,
                'maxScore' => 75,
            ],
            'levelDistribution' => $levelDistribution,
            'recentAssessments' => $recentAssessments,
        ]);
    }
}
