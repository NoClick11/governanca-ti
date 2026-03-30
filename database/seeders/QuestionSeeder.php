<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Option;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Seed de 15 questões predefinidas de maturidade de TI.
     */
    public function run(): void
    {
        $questions = [
            [
                'title' => 'Planejamento Estratégico de TI',
                'description' => 'Alinhamento entre as metas de tecnologia e os objetivos estratégicos da organização.',
                'options' => [
                    ['text' => 'Ausência total de planejamento de TI ou metas definidas.', 'weight' => 1],
                    ['text' => 'Planejamento informal, focado apenas em necessidades operacionais imediatas.', 'weight' => 2],
                    ['text' => 'Planejamento documentado, mas executado de forma isolada do negócio.', 'weight' => 3],
                    ['text' => 'TI integrada ao planejamento estratégico anual, com metas claras e aprovadas.', 'weight' => 4],
                    ['text' => 'TI como motor de estratégia, com planejamento contínuo e revisões trimestrais dialógicas.', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Estrutura de Governança de TI',
                'description' => 'Mecanismos de decisão, responsabilidades e frameworks (COBIT/ITIL) aplicados.',
                'options' => [
                    ['text' => 'Decisões centralizadas em indivíduos sem processos formais.', 'weight' => 1],
                    ['text' => 'Práticas básicas de governança implementadas de forma reativa.', 'weight' => 2],
                    ['text' => 'Existência de comitê de TI com pautas regulares de investimento.', 'weight' => 3],
                    ['text' => 'Frameworks de mercado (ITIL/COBIT) adotados e adaptados à realidade da empresa.', 'weight' => 4],
                    ['text' => 'Governança madura com auditoria, transparência total e melhoria contínua dos processos.', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Segurança da Informação e Cibersegurança',
                'description' => 'Proteção de dados, políticas de segurança e resposta a ameaças.',
                'options' => [
                    ['text' => 'Apenas proteções básicas (antivírus) sem políticas formais.', 'weight' => 1],
                    ['text' => 'Política de segurança existente, mas pouco divulgada ou aplicada.', 'weight' => 2],
                    ['text' => 'Controles técnicos robustos (Firewall, MFA) e política documentada.', 'weight' => 3],
                    ['text' => 'SGSI implementado com treinamentos constantes e plano de resposta a incidentes.', 'weight' => 4],
                    ['text' => 'Segurança ' . 'by design' . ' com monitoramento 24/7 (SOC) e conformidade ISO 27001.', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Gestão de Riscos de Tecnologia',
                'description' => 'Identificação, análise e mitigação de riscos tecnológicos.',
                'options' => [
                    ['text' => 'Riscos não são formalmente identificados ou analisados.', 'weight' => 1],
                    ['text' => 'Análise de riscos feita apenas após a ocorrência de incidentes críticos.', 'weight' => 2],
                    ['text' => 'Inventário de riscos de TI atualizado periodicamente.', 'weight' => 3],
                    ['text' => 'Matriz de riscos integrada ao negócio com planos de mitigação ativos.', 'weight' => 4],
                    ['text' => 'Monitoramento proativo de riscos com indicadores KRI e apetite ao risco definido.', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Continuidade de Negócio e Recuperação de Desastres (DR)',
                'description' => 'Capacidade da organização manter ou retomar operações em caso de falhas graves.',
                'options' => [
                    ['text' => 'Inexistência de planos de continuidade ou DR.', 'weight' => 1],
                    ['text' => 'Plano de DR rudimentar focado apenas em restauração de servidores.', 'weight' => 2],
                    ['text' => 'Processos de continuidade documentados para serviços críticos.', 'weight' => 3],
                    ['text' => 'Estratégia de DR testada anualmente com RTO e RPO definidos.', 'weight' => 4],
                    ['text' => 'Resiliência total com alta disponibilidade e failover automatizado multisite.', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Gestão de Serviços de TI (ITSM)',
                'description' => 'Maturidade nos processos de suporte, incidentes e atendimento ao usuário.',
                'options' => [
                    ['text' => 'Suporte informal via canais diretos (chat/telefone) sem registro.', 'weight' => 1],
                    ['text' => 'Central de serviços básica com registro de chamados mas sem SLAs.', 'weight' => 2],
                    ['text' => 'Gestão de incidentes e requisições com SLAs definidos e monitorados.', 'weight' => 3],
                    ['text' => 'Processos de problemas e mudanças integrados à gestão de serviços.', 'weight' => 4],
                    ['text' => 'Autoatendimento avançado com base de conhecimento e melhoria contínua (KCS).', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Estratégia de Backup e Proteção de Dados',
                'description' => 'Garantia de integridade e disponibilidade dos dados históricos e operacionais.',
                'options' => [
                    ['text' => 'Backups realizados de forma manual e inconsistente.', 'weight' => 1],
                    ['text' => 'Rotinas automatizadas de backup sem testes de restauração freqüentes.', 'weight' => 2],
                    ['text' => 'Política de backup 3-2-1 implementada com validações automáticas.', 'weight' => 3],
                    ['text' => 'Recuperação testada mensalmente com RPO e RTO garantidos por contrato.', 'weight' => 4],
                    ['text' => 'Proteção contra Ransomware (Immutable Backup) e restauração instantânea.', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Gestão de Ativos e Ciclo de Vida de TI',
                'description' => 'Controle de hardware, software, licenças e descarte tecnológico.',
                'options' => [
                    ['text' => 'Ausência de inventário atualizado de ativos.', 'weight' => 1],
                    ['text' => 'Inventário manual focado apenas em hardware físico.', 'weight' => 2],
                    ['text' => 'Gestão centralizada de ativos e conformidade básica de licenças.', 'weight' => 3],
                    ['text' => 'Ciclo de vida completo mapeado, da aquisição ao descarte sustentável.', 'weight' => 4],
                    ['text' => 'CMDB maduro integrado aos processos de mudança e financeiros.', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Engenharia de Software e Práticas DevOps',
                'description' => 'Qualidade no desenvolvimento, automação de testes e ciclos de entrega.',
                'options' => [
                    ['text' => 'Desenvolvimento sem metodologias ou padrões definidos.', 'weight' => 1],
                    ['text' => 'Adoção pontual de métodos ágeis sem automação de entrega.', 'weight' => 2],
                    ['text' => 'Controle de versão e ambientes de teste segregados da produção.', 'weight' => 3],
                    ['text' => 'Pipelinas de CI/CD, testes automatizados e code review sistêmico.', 'weight' => 4],
                    ['text' => 'Cultura DevOps madura com observabilidade e deploys contínuos.', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Terceirização e Gestão de Fornecedores Cloud',
                'description' => 'Governança sobre prestadores de serviços e infraestrutura em nuvem.',
                'options' => [
                    ['text' => 'Contratações informais sem SLAs ou critérios técnicos claros.', 'weight' => 1],
                    ['text' => 'Gestão de contratos básica, focada apenas em renovações.', 'weight' => 2],
                    ['text' => 'Avaliação regular de performance de fornecedores críticos.', 'weight' => 3],
                    ['text' => 'Estratégia de nuvem (Cloud-first) com governança de segurança e custos.', 'weight' => 4],
                    ['text' => 'Gestão de parceiros estratégicos com inovação conjunta e SLAs de negócio.', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Capacitação e Desenvolvimento de Talentos de TI',
                'description' => 'Investimento em competências técnicas e culturais da equipe interna.',
                'options' => [
                    ['text' => 'Inexistência de planos de treinamento ou desenvolvimento.', 'weight' => 1],
                    ['text' => 'Treinamentos esporádicos orientados apenas a correções de problemas.', 'weight' => 2],
                    ['text' => 'Plano anual de capacitação com incentivo a certificações.', 'weight' => 3],
                    ['text' => 'Trilhas de carreira e desenvolvimento de ' . 'soft skills' . ' estruturadas.', 'weight' => 4],
                    ['text' => 'Cultura de aprendizado contínuo com tempo dedicado à inovação.', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Conformidade, LGPD e Privacidade',
                'description' => 'Aderência a regulamentos setoriais e proteção de dados pessoais.',
                'options' => [
                    ['text' => 'Negligência em relação a legislações e normas de dados.', 'weight' => 1],
                    ['text' => 'Início da jornada de conformidade com LGPD (mapeamento básico).', 'weight' => 2],
                    ['text' => 'Processos de privacidade documentados e DPO nomeado.', 'weight' => 3],
                    ['text' => 'Conformidade plena com auditorias externas e Privacy ' . 'by Design' . '.', 'weight' => 4],
                    ['text' => 'Cultura de privacidade integrada ao DNA da empresa e dos produtos.', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Gestão de Mudanças e Configuração',
                'description' => 'Controle sobre alterações no ambiente produtivo para garantir estabilidade.',
                'options' => [
                    ['text' => 'Mudanças realizadas ' . 'ad-hoc' . ' sem registro ou aprovação.', 'weight' => 1],
                    ['text' => 'Registro básico de mudanças, mas sem análise clara de impacto.', 'weight' => 2],
                    ['text' => 'Processo formal de aprovação (CAB) para mudanças críticas.', 'weight' => 3],
                    ['text' => 'Gestão de configuração integrada às mudanças com rollback planejado.', 'weight' => 4],
                    ['text' => 'Mudanças automatizadas e seguras via infraestrutura como código (IaC).', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Monitoramento e Observabilidade de Sistemas',
                'description' => 'Visibilidade sobre a saúde dos serviços e proatividade na detecção de falhas.',
                'options' => [
                    ['text' => 'Monitoramento inexistente; erros descobertos pelos usuários.', 'weight' => 1],
                    ['text' => 'Monitoramento apenas de disponibilidade (Up/Down) básica.', 'weight' => 2],
                    ['text' => 'Dashboards de performance e alertas automáticos para infraestrutura.', 'weight' => 3],
                    ['text' => 'Observabilidade completa (Logs, Metrics, Traces) de ponta a ponta.', 'weight' => 4],
                    ['text' => 'Detecção proativa de anomalias com AIOps e autocorreção.', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Inovação e Transformação Digital',
                'description' => 'Capacidade da TI de gerar novas oportunidades de valor para o negócio.',
                'options' => [
                    ['text' => 'TI vista como centro de custo puramente operacional.', 'weight' => 1],
                    ['text' => 'Pequenas melhorias em processos existentes via tecnologia.', 'weight' => 2],
                    ['text' => 'Roadmap de transformação digital definido e com orçamento.', 'weight' => 3],
                    ['text' => 'TI como parceiro estratégico que propõe novos produtos e modelos.', 'weight' => 4],
                    ['text' => 'Liderança em inovação disruptiva com experimentos constantes e agilidade.', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Governança de Dados e Business Intelligence (BI)',
                'description' => 'Tratamento dos dados como ativo e uso para decisões inteligentes.',
                'options' => [
                    ['text' => 'Dados dispersos e sem padronização ou qualidade garantida.', 'weight' => 1],
                    ['text' => 'Relatórios manuais em planilhas dependentes de indivíduos.', 'weight' => 2],
                    ['text' => 'Ambiente de Analytics estruturado com fonte única da verdade (Single Source).', 'weight' => 3],
                    ['text' => 'Dashboards em tempo real integrados à tomada de decisão executiva.', 'weight' => 4],
                    ['text' => 'Cultura data-driven com uso avançado de IA e modelos preditivos.', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Gestão Financeira de TI (FinOps) e Orçamento',
                'description' => 'Eficiência no uso do capital, controle de custos de nuvem e transparência financeira.',
                'options' => [
                    ['text' => 'Falta de visibilidade e controle sobre os gastos de TI.', 'weight' => 1],
                    ['text' => 'Orçamento fixo gerenciado de forma reativa a cada trimestre.', 'weight' => 2],
                    ['text' => 'Monitoramento detalhado de custos operacionais e Capex/Opex.', 'weight' => 3],
                    ['text' => 'Otimização de custos de nuvem (FinOps) ativa e integrada ao time técnico.', 'weight' => 4],
                    ['text' => 'Gestão financeira estratégica com ROI e TCO medidos por serviço.', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Experiência Digital do Colaborador (DEX)',
                'description' => 'Qualidade das ferramentas e ambiente digital fornecido aos funcionários.',
                'options' => [
                    ['text' => 'Ferramentas defasadas que geram alta fricção e baixa produtividade.', 'weight' => 1],
                    ['text' => 'Suporte a infraestrutura básica para trabalho (Hardware/Email).', 'weight' => 2],
                    ['text' => 'Ambiente digital padronizado com ferramentas de colaboração modernas.', 'weight' => 3],
                    ['text' => 'Foco na jornada do colaborador com automação de processos internos.', 'weight' => 4],
                    ['text' => 'Experiência digital excepcional com suporte proativo e agilidade no onboarding.', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Arquitetura Tecnológica e Modernização',
                'description' => 'Gestão da dívida técnica e evolução da arquitetura para escalabilidade.',
                'options' => [
                    ['text' => 'Sistemas legados críticos sem plano de evolução ou suporte.', 'weight' => 1],
                    ['text' => 'Manutenção constante de legados com pouca inovação arquitetural.', 'weight' => 2],
                    ['text' => 'Roadmap de modernização para transição para microserviços ou SaaS.', 'weight' => 3],
                    ['text' => 'Arquitetura moderna, escalável e focada em APIs/Integrações.', 'weight' => 4],
                    ['text' => 'Simplicidade radical com arquitetura resiliente e evolução contínua.', 'weight' => 5],
                ],
            ],
            [
                'title' => 'Cultura Proativa e Mindset de Melhoria Contínua',
                'description' => 'Orientação da equipe de TI para resultados, qualidade e aprendizado com falhas.',
                'options' => [
                    ['text' => 'Cultura de contenção de custos e medo de mudanças.', 'weight' => 1],
                    ['text' => 'Equipe reativa focada apenas em "apagar incêndios".', 'weight' => 2],
                    ['text' => 'Busca regular por melhores práticas e automação de tarefas manuais.', 'weight' => 3],
                    ['text' => 'Cultura de responsabilidade (Ownership) e análises de falhas sem culpa.', 'weight' => 4],
                    ['text' => 'Mindset de excelência operacional com foco total no sucesso do cliente.', 'weight' => 5],
                ],
            ],
        ];


        foreach ($questions as $index => $questionData) {
            $question = Question::updateOrCreate(
                ['title' => $questionData['title']],
                [
                    'description' => $questionData['description'],
                    'order' => $index + 1,
                    'is_active' => true,
                ]
            );

            foreach ($questionData['options'] as $optIndex => $optionData) {
                Option::updateOrCreate(
                    [
                        'question_id' => $question->id,
                        'text' => $optionData['text'],
                    ],
                    [
                        'weight' => $optionData['weight'],
                        'order' => $optIndex + 1,
                    ]
                );
            }
        }
    }
}
