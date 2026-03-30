<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Cria o usuário Admin Global padrão.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@governanca.com'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('password'),
                'role' => 'admin_global',
                'company_id' => null,
                'email_verified_at' => now(),
            ]
        );
    }
}
