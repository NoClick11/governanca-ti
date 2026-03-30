<?php

namespace Database\Seeders;

use App\Models\MaturityIndicator;
use Illuminate\Database\Seeder;

class MaturityIndicatorSeeder extends Seeder
{
    /**
     * Seed dos indicadores de nível de maturidade de TI.
     */
    public function run(): void
    {
        MaturityIndicator::query()->delete();

        $indicators = [
            ['name' => 'Maturidade dos processos de TI'],
            ['name' => 'Uso de ferramentas de TI'],
            ['name' => 'Nível de serviço'],
            ['name' => 'Alinhamento com os objetivos estratégicos'],
            ['name' => 'Governança de TI'],
            ['name' => 'Gestão de riscos'],
            ['name' => 'Cultura de TI'],
        ];

        foreach ($indicators as $indicator) {
            MaturityIndicator::create($indicator);
        }
    }
}
