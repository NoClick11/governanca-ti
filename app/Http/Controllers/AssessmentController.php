<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\MaturityIndicator;
use App\Models\MaturityLevel;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssessmentController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Empresas que o usuário pode acessar
        $accessibleCompanies = \App\Models\Company::query()
            ->when(!$user->isAdminGlobal(), function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('id', $user->company_id);
            })
            ->get(['id', 'name']);

        $assessments = Assessment::query()
            ->with(['company', 'user'])
            ->when(!$user->isAdminGlobal(), function ($query) use ($user) {
                // Usuário vê todas das empresas que gerencia
                $query->whereIn('company_id', 
                    \App\Models\Company::where('user_id', $user->id)
                        ->orWhere('id', $user->company_id)
                        ->pluck('id')
                );
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($a) => [
                'id' => $a->id,
                'company_name' => $a->company->name,
                'user_name' => $a->user->name,
                'status' => $a->status,
                'total_score' => $a->total_score,
                'max_score' => $a->max_score ?? 75,
                'maturity_level' => $a->maturity_level,
                'progress' => $a->getProgress(),
                'created_at' => $a->created_at->format('d/m/Y H:i'),
                'completed_at' => $a->completed_at?->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Assessments/Index', [
            'assessments' => $assessments,
            'companies' => $accessibleCompanies,
            'filters' => $request->only(['status']),
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $companyId = $request->input('company_id') ?? $user->company_id;

        if (!$companyId || !$user->canAccessCompany($companyId)) {
            return redirect()->route('assessments.index')
                ->with('error', 'Selecione uma empresa válida para iniciar a avaliação.');
        }

        // Cria a avaliação
        $assessment = Assessment::create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'status' => 'in_progress',
        ]);

        return redirect()->route('assessments.show', $assessment);
    }

    public function show(Request $request, Assessment $assessment): Response|RedirectResponse
    {
        $user = $request->user();

        // Verifica acesso
        if (!$user->isAdminGlobal() && $assessment->company_id !== $user->company_id) {
            abort(403);
        }

        // Se já completada, redireciona para o relatório
        if ($assessment->isCompleted()) {
            return redirect()->route('assessments.report', $assessment);
        }

        // Carrega questões ativas com suas opções do cache resiliente no modelo
        $questions = Question::getActiveCached();

        $questions = $questions->map(function ($question) use ($assessment) {
                $answer = $assessment->answers()
                    ->where('question_id', $question->id)
                    ->first();

                return [
                    'id' => $question->id,
                    'title' => $question->title,
                    'description' => $question->description,
                    'order' => $question->order,
                    'options' => $question->options->map(fn ($opt) => [
                        'id' => $opt->id,
                        'text' => $opt->text,
                        'weight' => $opt->weight,
                        'order' => $opt->order,
                    ]),
                    'selected_option_id' => $answer?->option_id,
                ];
            });

        $progress = $assessment->getProgress();

        return Inertia::render('Assessments/Take', [
            'assessment' => [
                'id' => $assessment->id,
                'status' => $assessment->status,
            ],
            'questions' => $questions,
            'progress' => $progress,
        ]);
    }

    public function storeAnswer(Request $request, Assessment $assessment): RedirectResponse
    {
        $user = $request->user();

        if (!$user->isAdminGlobal() && $assessment->user_id !== $user->id) {
            abort(403);
        }

        if ($assessment->isCompleted()) {
            return redirect()->route('assessments.report', $assessment)
                ->with('error', 'Esta avaliação já foi finalizada.');
        }

        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'option_id' => 'required|exists:options,id',
        ]);

        // Upsert: atualiza a resposta se já existir, senão cria
        AssessmentAnswer::updateOrCreate(
            [
                'assessment_id' => $assessment->id,
                'question_id' => $validated['question_id'],
            ],
            [
                'option_id' => $validated['option_id'],
            ]
        );

        return back();
    }

    public function complete(Request $request, Assessment $assessment): RedirectResponse
    {
        $user = $request->user();

        if (!$user->isAdminGlobal() && $assessment->user_id !== $user->id) {
            abort(403);
        }

        if ($assessment->isCompleted()) {
            return redirect()->route('assessments.report', $assessment);
        }

        // Verifica se todas as questões foram respondidas
        $totalQuestions = Question::active()->count();
        $answeredQuestions = $assessment->answers()->count();

        if ($answeredQuestions < $totalQuestions) {
            return back()->with('error', 'Responda todas as questões antes de finalizar.');
        }

        // Finaliza a avaliação
        $assessment->complete();

        return redirect()->route('assessments.report', $assessment)
            ->with('success', 'Avaliação finalizada com sucesso!');
    }

    public function report(Request $request, Assessment $assessment): Response
    {
        $user = $request->user();

        if (!$user->isAdminGlobal() && $assessment->company_id !== $user->company_id) {
            abort(403);
        }

        $assessment->load(['company', 'user', 'answers.question', 'answers.option']);

        $maturityLevel = MaturityLevel::getForScore($assessment->total_score ?? 0, $assessment->max_score ?? 75);
        $allLevels = MaturityLevel::getAllOrdered();
        $indicators = MaturityIndicator::getAllCached();
        $maxScore = $assessment->max_score ?? 75;

        // Pontuação por questão
        $questionScores = $assessment->answers->map(fn ($answer) => [
            'question' => $answer->question->title,
            'selected_option' => $answer->option->text,
            'weight' => $answer->option->weight,
            'max_weight' => 5,
        ]);

        return Inertia::render('Assessments/Report', [
            'assessment' => [
                'id' => $assessment->id,
                'company_name' => $assessment->company->name,
                'user_name' => $assessment->user->name,
                'total_score' => $assessment->total_score,
                'maturity_level' => $assessment->maturity_level,
                'status' => $assessment->status,
                'completed_at' => $assessment->completed_at?->format('d/m/Y H:i'),
                'created_at' => $assessment->created_at->format('d/m/Y H:i'),
            ],
            'maturityLevel' => $maturityLevel ? [
                'name' => $maturityLevel->name,
                'description' => $maturityLevel->description,
                'improvements' => $maturityLevel->improvements,
                'color' => $maturityLevel->color,
                'min_score' => $maturityLevel->min_score,
                'max_score' => $maturityLevel->max_score,
            ] : null,
            'allLevels' => $allLevels->map(fn ($l) => [
                'name' => $l->name,
                'min_score' => $l->min_score,
                'max_score' => $l->max_score,
                'color' => $l->color,
                'description' => $l->description,
            ]),
            'indicators' => $indicators->map(fn ($i) => [
                'name' => $i->name,
                'description' => $i->description,
            ]),
            'questionScores' => $questionScores,
            'maxScore' => $maxScore,
        ]);
    }
}
