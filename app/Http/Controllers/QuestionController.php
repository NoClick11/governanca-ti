<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Option;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuestionController extends Controller
{
    public function index(Request $request): Response
    {
        $questions = Question::query()
            ->withCount('options')
            ->when($request->input('search'), function ($query, $search) {
                $query->where('title', 'ilike', "%{$search}%");
            })
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Questions/Index', [
            'questions' => $questions,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        $nextOrder = (Question::max('order') ?? 0) + 1;

        return Inertia::render('Questions/Form', [
            'nextOrder' => $nextOrder,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'order' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'options' => 'required|array|min:2',
            'options.*.text' => 'required|string|max:500',
            'options.*.weight' => 'required|integer|min:0',
            'options.*.order' => 'required|integer|min:1',
        ]);

        $question = Question::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'order' => $validated['order'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        foreach ($validated['options'] as $optionData) {
            $question->options()->create($optionData);
        }

        return redirect()->route('questions.index')
            ->with('success', 'Questão criada com sucesso!');
    }

    public function edit(Question $question): Response
    {
        $question->load('options');

        return Inertia::render('Questions/Form', [
            'question' => $question,
        ]);
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'order' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'options' => 'required|array|min:2',
            'options.*.id' => 'nullable|integer',
            'options.*.text' => 'required|string|max:500',
            'options.*.weight' => 'required|integer|min:0',
            'options.*.order' => 'required|integer|min:1',
        ]);

        $question->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'order' => $validated['order'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Sync options: atualiza existentes, cria novas, remove as que não vieram
        $incomingIds = collect($validated['options'])
            ->pluck('id')
            ->filter()
            ->toArray();

        // Remove opções que não estão no request
        $question->options()->whereNotIn('id', $incomingIds)->delete();

        // Cria ou atualiza opções
        foreach ($validated['options'] as $optionData) {
            if (!empty($optionData['id'])) {
                Option::where('id', $optionData['id'])
                    ->where('question_id', $question->id)
                    ->update([
                        'text' => $optionData['text'],
                        'weight' => $optionData['weight'],
                        'order' => $optionData['order'],
                    ]);
            } else {
                $question->options()->create($optionData);
            }
        }

        return redirect()->route('questions.index')
            ->with('success', 'Questão atualizada com sucesso!');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $question->delete(); // cascade deletes options

        return redirect()->route('questions.index')
            ->with('success', 'Questão excluída com sucesso!');
    }
}
