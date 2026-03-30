<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $users = User::query()
            ->with('company')
            ->when(!$user->isAdminGlobal(), function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                });
            })
            ->when($request->input('role'), function ($query, $role) {
                $query->where('role', $role);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $companies = $user->isAdminGlobal()
            ? Company::where('is_active', true)->get(['id', 'name'])
            : collect();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'companies' => $companies,
            'filters' => $request->only(['search', 'role']),
            'roles' => [
                ['value' => 'admin_global', 'label' => 'Admin Global'],
                ['value' => 'admin_cliente', 'label' => 'Usuário'],
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $request->user();

        $companies = $user->isAdminGlobal()
            ? Company::where('is_active', true)->get(['id', 'name'])
            : collect();

        $availableRoles = $user->isAdminGlobal()
            ? [
                ['value' => 'admin_global', 'label' => 'Admin Global'],
                ['value' => 'admin_cliente', 'label' => 'Usuário'],
            ]
            : [
                ['value' => 'admin_cliente', 'label' => 'Usuário'],
            ];

        return Inertia::render('Users/Form', [
            'companies' => $companies,
            'roles' => $availableRoles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin_global,admin_cliente',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        // Usuário só pode criar outros usuários na mesma empresa
        if ($user->isAdminCliente()) {
            $validated['role'] = 'admin_cliente';
            $validated['company_id'] = $user->company_id;
        }

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    public function edit(Request $request, User $user): Response
    {
        $currentUser = $request->user();

        // Usuário só pode editar users da mesma empresa
        if ($currentUser->isAdminCliente() && $user->company_id !== $currentUser->company_id) {
            abort(403);
        }

        $companies = $currentUser->isAdminGlobal()
            ? Company::where('is_active', true)->get(['id', 'name'])
            : collect();

        $availableRoles = $currentUser->isAdminGlobal()
            ? [
                ['value' => 'admin_global', 'label' => 'Admin Global'],
                ['value' => 'admin_cliente', 'label' => 'Usuário'],
            ]
            : [
                ['value' => 'admin_cliente', 'label' => 'Usuário'],
            ];

        return Inertia::render('Users/Form', [
            'editUser' => $user->load('company'),
            'companies' => $companies,
            'roles' => $availableRoles,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $currentUser = $request->user();

        if ($currentUser->isAdminCliente() && $user->company_id !== $currentUser->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin_global,admin_cliente',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        if ($currentUser->isAdminCliente()) {
            $validated['role'] = $user->role; // não pode alterar role
            $validated['company_id'] = $currentUser->company_id;
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $currentUser = $request->user();

        if ($user->id === $currentUser->id) {
            return redirect()->route('users.index')
                ->with('error', 'Você não pode excluir sua própria conta.');
        }

        if ($currentUser->isAdminCliente() && $user->company_id !== $currentUser->company_id) {
            abort(403);
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuário excluído com sucesso!');
    }
}
