<?php

namespace Database\Seeders;

use App\Models\MaturityLevel;
use Illuminate\Database\Seeder;

class MaturityLevelSeeder extends Seeder
{
    /**
     * Seed dos 4 níveis de maturidade de TI solicitados.
     */
    public function run(): void
    {
        // Limpar cache para evitar dados antigos/corrompidos
        \Illuminate\Support\Facades\Cache::forget('maturity_levels.ordered');

        // Limpar níveis antigos se existirem
        MaturityLevel::query()->delete();

        $levels = [
            [
                'name' => 'Artesanal',
                'min_score' => 0,
                'max_score' => 24,
                'description' => 'A organização apresenta processos de TI informais, manuais e altamente reativos. A dependência de esforços individuais é alta, com pouca ou nenhuma padronização. Riscos de segurança e continuidade são elevados.',
                'improvements' => "• Realizar um inventário completo de ativos (Hardware e Software);\n• Centralizar o suporte em um canal oficial de chamados;\n• Implementar rotinas automáticas de backup para dados críticos;\n• Documentar senhas e configurações essenciais em local seguro.",
                'color' => '#ef4444',
                'order' => 1,
            ],
            [
                'name' => 'Eficiente',
                'min_score' => 25,
                'max_score' => 49,
                'description' => 'Processos básicos de TI estão definidos e são executados de forma consistente em algumas áreas. Há um esforço inicial de padronização buscando a redução de falhas operacionais e melhor aproveitamento de recursos.',
                'improvements' => "• Implementar ferramentas de monitoramento proativo de disponibilidade;\n• Padronizar as imagens de instalação de computadores e servidores;\n• Definir acordos de nível de serviço (SLA) com os departamentos;\n• Instituir uma gestão formal de ativos e licenças de software.",
                'color' => '#f97316',
                'order' => 2,
            ],
            [
                'name' => 'Eficaz',
                'min_score' => 50,
                'max_score' => 74,
                'description' => 'Os processos de TI estão consolidados, documentados e integrados às necessidades do negócio. A TI gera valor real e previsível, com métricas de desempenho estabelecidas e monitoramento constante.',
                'improvements' => "• Estabelecer indicadores chave de performance (KPIs) para gestão;\n• Automatizar tarefas complexas de infraestrutura e serviços (Nuvem);\n• Criar um Comitê de Governança de TI com participação da diretoria;\n• Planejar o ciclo de vida e a atualização tecnológica periódica.",
                'color' => '#22c55e',
                'order' => 3,
            ],
            [
                'name' => 'Estratégico',
                'min_score' => 75,
                'max_score' => 100,
                'description' => 'A TI é um motor de inovação e vantagem competitiva, totalmente alinhada à estratégia corporativa. Os processos são continuamente otimizados e a organização antecipa tendências tecnológicas.',
                'improvements' => "• Focar em inovação e novas tecnologias para gerar diferencial de negócio;\n• Atuar como consultoria interna para a transformação digital da empresa;\n• Desenvolver uma cultura ágil e de alta performance na equipe;\n• Validar e testar periodicamente os planos de recuperação de desastres.",
                'color' => '#06b6d4',
                'order' => 4,
            ],
        ];

        foreach ($levels as $level) {
            MaturityLevel::create($level);
        }
    }
}
