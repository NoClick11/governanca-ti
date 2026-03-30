<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $companies = Company::query()
            ->when(!$user->isAdminGlobal(), function ($query) use ($user) {
                // admin_cliente vê as que criou e a dele (mesmo se criada por outro, ex: no registro)
                $query->where('user_id', $user->id)
                    ->orWhere('id', $user->company_id);
            })
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('cnpj', 'ilike', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Companies/Index', [
            'companies' => $companies,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Companies/Form');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18|unique:companies,cnpj',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['is_active'] = $validated['is_active'] ?? true;

        Company::create($validated);

        return redirect()->route('companies.index')
            ->with('success', 'Empresa cadastrada com sucesso!');
    }

    public function edit(Company $company): Response
    {
        $this->authorizeAccess($company);

        return Inertia::render('Companies/Form', [
            'company' => $company,
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $this->authorizeAccess($company);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18|unique:companies,cnpj,' . $company->id,
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $company->update($validated);

        return redirect()->route('companies.index')
            ->with('success', 'Empresa atualizada com sucesso!');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $this->authorizeAccess($company);

        // Não permitir deletar a empresa vinculada ao usuário no registro
        if ($company->id === auth()->user()->company_id) {
            return back()->with('error', 'Você não pode excluir sua empresa principal.');
        }

        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Empresa excluída com sucesso!');
    }

    /**
     * Auxiliar para autorização.
     */
    private function authorizeAccess(Company $company): void
    {
        $user = auth()->user();
        if ($user->isAdminGlobal()) {
            return;
        }

        // Permite se for o criador OU se for a empresa principal dele
        if ($company->user_id !== $user->id && $company->id !== $user->company_id) {
            abort(403, 'Você não tem permissão para gerenciar esta empresa.');
        }
    }
}
