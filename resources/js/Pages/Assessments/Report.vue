<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    assessment: Object,
    maturityLevel: Object,
    allLevels: Array,
    indicators: Array,
    questionScores: Array,
    maxScore: Number,
});

const scorePct = computed(() =>
    Math.round((props.assessment.total_score / props.maxScore) * 100)
);

// Gauge Chart
const gaugeOptions = computed(() => ({
    chart: {
        type: 'radialBar',
        height: 350,
        sparkline: { enabled: false },
    },
    plotOptions: {
        radialBar: {
            startAngle: -135,
            endAngle: 135,
            hollow: {
                size: '60%',
                margin: 0,
            },
            track: {
                background: '#1e293b',
                strokeWidth: '100%',
                margin: 0,
            },
            dataLabels: {
                name: {
                    offsetY: -20,
                    color: props.maturityLevel?.color || '#94a3b8',
                    fontSize: '16px',
                    fontWeight: '700',
                },
                value: {
                    color: '#f1f5f9',
                    fontSize: '44px',
                    fontWeight: '800',
                    show: true,
                    offsetY: 5,
                    formatter: () => `${props.assessment.total_score}`,
                },
            },
        },
    },
    fill: {
        type: 'gradient',
        gradient: {
            shade: 'dark',
            type: 'horizontal',
            colorStops: [
                { offset: 0, color: '#ef4444' },
                { offset: 33, color: '#f97316' },
                { offset: 66, color: '#22c55e' },
                { offset: 100, color: '#06b6d4' },
            ],
        },
    },
    stroke: { lineCap: 'round' },
    labels: [props.maturityLevel?.name || 'N/A'],
}));

const gaugeSeries = computed(() => [scorePct.value]);

// Bar Chart por questão
const barOptions = computed(() => ({
    chart: {
        type: 'bar',
        height: 400,
        toolbar: { show: false },
        background: 'transparent',
    },
    plotOptions: {
        bar: {
            horizontal: true,
            borderRadius: 6,
            barHeight: '60%',
            distributed: true,
        },
    },
    colors: props.questionScores.map(q => {
        const pct = q.weight / q.max_weight;
        if (pct <= 0.25) return '#ef4444'; // Artesanal
        if (pct <= 0.5) return '#f97316';  // Eficiente
        if (pct <= 0.75) return '#22c55e';  // Efetiva
        return '#06b6d4';                  // Estratégica
    }),
    dataLabels: {
        enabled: true,
        formatter: (val) => `${val}/5`,
        style: { colors: ['#fff'], fontWeight: 600, fontSize: '11px' },
    },
    xaxis: {
        categories: props.questionScores.map(q =>
            q.question.length > 35 ? q.question.substring(0, 35) + '...' : q.question
        ),
        max: 5,
        labels: { style: { colors: '#94a3b8', fontSize: '11px' } },
    },
    yaxis: {
        labels: { style: { colors: '#94a3b8', fontSize: '11px' }, maxWidth: 250 },
    },
    grid: { borderColor: '#1e293b', strokeDashArray: 4, xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
    legend: { show: false },
    theme: { mode: 'dark' },
    tooltip: {
        theme: 'dark',
        custom: ({ dataPointIndex }) => {
            const q = props.questionScores[dataPointIndex];
            return `<div class="p-3">
                <p class="font-bold text-sm">${q.question}</p>
                <p class="text-xs mt-1 text-gray-400">${q.selected_option}</p>
                <p class="text-xs mt-1">Peso: <strong>${q.weight}</strong> / ${q.max_weight}</p>
            </div>`;
        },
    },
}));

const barSeries = computed(() => [{
    name: 'Pontuação',
    data: props.questionScores.map(q => q.weight),
}]);

// --- PDTI LOGIC & METRICS ---
import { ref } from 'vue';

const activeTab = ref('maturity'); // 'maturity' ou 'pdti'
const activePdtiSubTab = ref('dimensions'); // 'dimensions', 'risks', 'roadmap', 'kpis'
const selectedRiskCell = ref(null); // { p, i } ou null
const selectedHorizonFilter = ref('all'); // 'all', 'curto', 'medio', 'longo'
const selectedPriorityFilter = ref('all'); // 'all', 'critica', 'alta', 'media', 'baixa'
const expandedInitiativeId = ref(null); // id para sanfona

const toggleInitiative = (id) => {
    expandedInitiativeId.value = expandedInitiativeId.value === id ? null : id;
};

// 1. Mapeamento de Dimensões
const dimensions = computed(() => {
    const map = {
        'Estratégia e Alinhamento': [1, 2, 10, 15, 17],
        'Segurança, Riscos e Resiliência': [3, 4, 5, 7, 12],
        'Gestão de Serviços e Operações': [6, 8, 13, 14, 18],
        'Tecnologia, Processos e Pessoas': [9, 11, 16, 19, 20]
    };
    
    return Object.entries(map).map(([name, questionNumbers]) => {
        let score = 0;
        let maxScore = questionNumbers.length * 5;
        const associatedGaps = [];

        questionNumbers.forEach(num => {
            const q = props.questionScores[num - 1];
            if (q) {
                score += q.weight;
                if (q.weight < 4) {
                    associatedGaps.push({
                        title: q.question,
                        selected: q.selected_option,
                        weight: q.weight
                    });
                }
            }
        });

        const percentage = Math.round((score / maxScore) * 100);
        let status = 'Consolidado';
        let color = '#10b981'; // emerald-500
        let bgLight = 'rgba(16, 185, 129, 0.1)';
        
        if (percentage < 50) {
            status = 'Crítico';
            color = '#ef4444'; // red-500
            bgLight = 'rgba(239, 68, 68, 0.1)';
        } else if (percentage < 80) {
            status = 'Em Evolução';
            color = '#f97316'; // orange-500
            bgLight = 'rgba(249, 115, 22, 0.1)';
        }

        return { name, score, maxScore, percentage, status, color, bgLight, gaps: associatedGaps };
    });
});

// 2. Mapeamento de Riscos
const risks = computed(() => {
    const riskDefinitions = {
        1: { risk: "Desalinhamento estratégico e investimentos inadequados em TI", impact: 4, category: "Estratégia" },
        2: { risk: "Processos decisórios ineficientes e centralização de poder técnico", impact: 4, category: "Estratégia" },
        3: { risk: "Vulnerabilidade a ciberataques, invasões e vazamento de dados confidenciais", impact: 5, category: "Segurança" },
        4: { risk: "Indefinição de apetite ao risco e incidentes sem mitigação prévia", impact: 4, category: "Segurança" },
        5: { risk: "Interrupção prolongada de serviços de negócio por desastres", impact: 5, category: "Resiliência" },
        6: { risk: "Ineficiência operacional de suporte e insatisfação das áreas de negócio", impact: 3, category: "Operações" },
        7: { risk: "Perda definitiva de dados históricos e operacionais vitais", impact: 5, category: "Resiliência" },
        8: { risk: "Desperdício financeiro com licenças de hardware/software subutilizadas ou multas", impact: 3, category: "Operações" },
        9: { risk: "Lentidão nas entregas de software e alta taxa de erros em produção", impact: 4, category: "Engenharia" },
        10: { risk: "Dependência de fornecedores externos sem salvaguardas contratuais (SLA)", impact: 4, category: "Estratégia" },
        11: { risk: "Perda de conhecimento crítico por alta rotatividade e falta de capacitação", impact: 3, category: "Pessoas" },
        12: { risk: "Multas severas e sanções administrativas por descumprimento da LGPD", impact: 5, category: "Conformidade" },
        13: { risk: "Instabilidade do ambiente produtivo causada por alterações não autorizadas", impact: 4, category: "Operações" },
        14: { risk: "Falta de detecção proativa de gargalos de performance e indisponibilidade de sistemas", impact: 4, category: "Operações" },
        15: { risk: "Obsolescência tecnológica acelerada frente aos concorrentes", impact: 4, category: "Inovação" },
        16: { risk: "Decisões corporativas baseadas em relatórios inconsistentes ou errôneos", impact: 4, category: "Engenharia" },
        17: { risk: "Estouro orçamentário constante em TI e falta de ROI em investimentos", impact: 4, category: "Finanças" },
        18: { risk: "Perda de produtividade interna por uso de ferramentas lentas ou defasadas", impact: 3, category: "Pessoas" },
        19: { risk: "Alto custo de sustentação de sistemas legados e impossibilidade de integração", impact: 4, category: "Engenharia" },
        20: { risk: "Atuação puramente reativa e baixo comprometimento com qualidade operacional", impact: 3, category: "Pessoas" },
    };

    return Object.entries(riskDefinitions).map(([id, def]) => {
        const q = props.questionScores[parseInt(id) - 1];
        if (!q) return null;

        // Probabilidade inversa à nota da resposta: Nota 1 -> Prob 5; Nota 2 -> Prob 4; Nota 3 -> Prob 3; Nota 4 -> Prob 2; Nota 5 -> Prob 1
        const probability = 6 - q.weight;
        const score = def.impact * probability;

        let riskLevel = 'Baixo';
        let color = '#10b981'; // emerald
        let bgLight = 'rgba(16, 185, 129, 0.15)';
        if (score >= 20) {
            riskLevel = 'Crítico';
            color = '#ef4444'; // red
            bgLight = 'rgba(239, 68, 68, 0.15)';
        } else if (score >= 12) {
            riskLevel = 'Alto';
            color = '#f97316'; // orange
            bgLight = 'rgba(249, 115, 22, 0.15)';
        } else if (score >= 8) {
            riskLevel = 'Médio';
            color = '#eab308'; // yellow
            bgLight = 'rgba(234, 179, 8, 0.15)';
        }

        return {
            id: parseInt(id),
            risk: def.risk,
            category: def.category,
            impact: def.impact,
            probability,
            score,
            level: riskLevel,
            color,
            bgLight,
            question: q.question,
            currentControl: q.selected_option
        };
    }).filter(Boolean);
});

// 3. Matriz de Calor 5x5
const riskMatrixGrid = computed(() => {
    const grid = [];
    for (let i = 5; i >= 1; i--) {
        const row = [];
        for (let p = 1; p <= 5; p++) {
            const cellRiscos = risks.value.filter(r => r.impact === i && r.probability === p);
            row.push({
                impact: i,
                probability: p,
                risks: cellRiscos,
                count: cellRiscos.length
            });
        }
        grid.push(row);
    }
    return grid;
});

// Filtro da tabela de riscos pela célula selecionada
const filteredRisks = computed(() => {
    if (!selectedRiskCell.value) return risks.value;
    const { p, i } = selectedRiskCell.value;
    return risks.value.filter(r => r.probability === p && r.impact === i);
});

// Função para selecionar célula da matriz de risco
const selectRiskCell = (p, i) => {
    if (selectedRiskCell.value && selectedRiskCell.value.p === p && selectedRiskCell.value.i === i) {
        selectedRiskCell.value = null; // desmarca se clicar de novo
    } else {
        selectedRiskCell.value = { p, i };
    }
};

// 4. Mapeamento de Iniciativas
const initiatives = computed(() => {
    const initDefinitions = {
        1: {
            title: "Formalização do PETI (Planejamento Estratégico de TI)",
            priority: "Alta",
            horizon: "Curto Prazo",
            timeframe: "0-6 meses",
            impact: "Alto Impacto",
            description: "Elaborar o Planejamento Estratégico de TI alinhado diretamente aos objetivos estratégicos da organização para garantir que a tecnologia atue como viabilizadora de negócios e propulsora de novas oportunidades.",
            deliverables: [
                "Documento do PETI estruturado e assinado pela diretoria corporativa",
                "Roadmap de projetos tecnológicos priorizados por retorno financeiro e de processo",
                "Definição de orçamento plurianual específico para CAPEX/OPEX de novas tecnologias"
            ],
            steps: [
                "Entrevistar diretores das principais áreas de negócio para mapear necessidades estratégicas",
                "Conduzir análise SWOT focada exclusivamente em Tecnologia e Sistemas",
                "Redigir a Visão e Missão de TI e desenhar o roadmap estratégico para 24 meses"
            ]
        },
        2: {
            title: "Implementação do Comitê de Governança de TI",
            priority: "Média",
            horizon: "Médio Prazo",
            timeframe: "6-12 meses",
            impact: "Médio Impacto",
            description: "Instituir um Comitê de Governança de TI com participação ativa da diretoria e áreas chaves do negócio para aprovação de investimentos, alinhamento técnico e gestão transparente de projetos.",
            deliverables: [
                "Regimento interno aprovado determinando papéis e alçadas de tomada de decisão",
                "Calendário oficial e pautas mensais de reuniões com atas registradas",
                "Matriz RACI formalizada de responsabilidade técnica e decisória de TI"
            ],
            steps: [
                "Redigir o rascunho do estatuto do Comitê de Governança",
                "Definir os membros fixos (TI, Financeiro, Operações e CEO/Diretoria)",
                "Agendar e realizar a primeira reunião de kick-off para estabelecer prioridades"
            ]
        },
        3: {
            title: "Segurança em Camadas & Fortalecimento de Credenciais",
            priority: "Crítica",
            horizon: "Curto Prazo",
            timeframe: "0-3 meses",
            impact: "Alto Impacto",
            description: "Implementar controles técnicos de segurança críticos, incluindo MFA em todos os sistemas corporativos e treinamentos constantes em cibersegurança para conscientização de toda a empresa.",
            deliverables: [
                "MFA (Autenticação de Múltiplos Fatores) ativado em 100% dos usuários e sistemas críticos",
                "Política de Segurança da Informação (PSI) aprovada e devidamente publicada",
                "Treinamento básico de conscientização concluído por todos os funcionários"
            ],
            steps: [
                "Ativar MFA mandatório na suíte de e-mails/produtividade e ERP corporativo",
                "Redigir a PSI definindo regras de senhas complexas, uso de dispositivos e acesso remoto",
                "Contratar ou formular trilha rápida de phishing e engenharia social para a equipe"
            ]
        },
        4: {
            title: "Mapeamento e Matriz de Riscos de TI",
            priority: "Média",
            horizon: "Médio Prazo",
            timeframe: "6-12 meses",
            impact: "Médio Impacto",
            description: "Identificar, classificar e registrar os principais riscos de tecnologia e cibersegurança da empresa, desenhando planos de mitigação ativos e integrados à gestão de riscos corporativos.",
            deliverables: [
                "Matriz de Riscos de TI (Controle de Probabilidade vs Impacto)",
                "Plano de Ação contendo controles técnicos mitigatórios para riscos classificados como altos",
                "Integração do processo de risco de TI no cronograma de auditoria da empresa"
            ],
            steps: [
                "Mapear ativos críticos and vulnerabilidades físicas e lógicas associadas",
                "Avaliar probabilidade e impacto financeiro/reputacional de cada cenário de falha",
                "Registrar planos específicos de contenção, transferência ou aceitação de riscos"
            ]
        },
        5: {
            title: "Elaboração do Plano de Recuperação de Desastres (DRP)",
            priority: "Alta",
            horizon: "Curto Prazo",
            timeframe: "3-6 meses",
            impact: "Alto Impacto",
            description: "Desenvolver e documentar um Plano de Recuperação de Desastres (DRP) robusto, especificando tempos aceitáveis de parada (RTO) e de perda de dados (RPO) para assegurar a resiliência corporativa.",
            deliverables: [
                "Plano de Recuperação de Desastres (DRP) documentado detalhadamente",
                "RTO (Tempo Máximo de Recuperação) e RPO (Ponto Máximo de Recuperação) acordados por serviço",
                "Relatório de simulação prática e testes anuais de contingência de infraestrutura"
            ],
            steps: [
                "Identificar os sistemas e serviços de infraestrutura críticos para a continuidade de vendas/faturamento",
                "Escrever passo a passo de restauração e transição de servidores e nuvens em caso de queda total",
                "Agendar simulação prática controlada fora do horário comercial com o time técnico"
            ]
        },
        6: {
            title: "Estruturação do Service Desk com SLA Formal",
            priority: "Média",
            horizon: "Curto Prazo",
            timeframe: "3-6 meses",
            impact: "Médio Impacto",
            description: "Organizar e centralizar o suporte de TI por meio de um canal único e oficial de chamados (Service Desk), estabelecendo Acordos de Nível de Serviço (SLAs) claros por severidade de incidente.",
            deliverables: [
                "Ferramenta de ITSM (Gestão de Serviços) implantada com fluxo de chamados ativo",
                "Catálogo de Serviços de TI detalhado e SLAs definidos de resposta e resolução",
                "Relatórios de performance operacional mensal demonstrando cumprimento de metas de SLA"
            ],
            steps: [
                "Selecionar e configurar um software básico de chamados de mercado (ex: Jira, Freshservice, GLPI)",
                "Divulgar amplamente à empresa o canal oficial (excluindo chamados diretos via chat individual)",
                "Definir SLAs (ex: Incidente Crítico resolvido em até 4h, Requisição básica em até 48h)"
            ]
        },
        7: {
            title: "Adoção da Regra de Backup 3-2-1 e Proteção Ransomware",
            priority: "Crítica",
            horizon: "Curto Prazo",
            timeframe: "0-3 meses",
            impact: "Alto Impacto",
            description: "Adotar rigorosamente o padrão de Backup 3-2-1 (3 cópias, 2 mídias diferentes, 1 offsite/nuvem) com proteção contra ransomwares (backups imutáveis) e validações mensais de integridade.",
            deliverables: [
                "Rotina de backup 3-2-1 em pleno funcionamento e automatizada",
                "Uso de armazenamento de nuvem criptografado isolado com proteção imutável (WORM)",
                "Relatório de testes de restore assinados e validados mensalmente"
            ],
            steps: [
                "Configurar rotina automatizada de cópia de segurança local e replicação em nuvem",
                "Habilitar funcionalidade de imutabilidade ou retenção estrita na nuvem de backup",
                "Definir no calendário técnico mensal a execução de testes de restauração de banco e arquivos"
            ]
        },
        8: {
            title: "Inventário Automatizado de Ativos de TI (CMDB)",
            priority: "Baixa",
            horizon: "Médio Prazo",
            timeframe: "6-12 meses",
            impact: "Médio Impacto",
            description: "Estruturar um processo de gestão centralizada de ativos para controlar hardware, licenças de software expirando e ciclo de vida tecnológico, evitando desperdícios e auditorias negativas.",
            deliverables: [
                "Planilha consolidada ou sistema automático de inventário físico e digital de ativos",
                "Controle ativo de licenças e subscrições de SaaS para evitar renovações indesejadas",
                "Política formalizada de descarte sustentável de lixo tecnológico corporativo"
            ],
            steps: [
                "Efetuar varredura de rede ou coleta física para catalogar todos os computadores, servidores e licenças",
                "Mapear vencimentos e custos associados de todos os softwares da empresa",
                "Configurar alertas automáticos de vencimento de licenças e certificados SSL"
            ]
        },
        9: {
            title: "Implementação de Pipelines CI/CD e Qualidade de Código",
            priority: "Média",
            horizon: "Longo Prazo",
            timeframe: "12-24 meses",
            impact: "Alto Impacto",
            description: "Substituir deploys manuais por fluxos de integração e entrega contínua (CI/CD) automatizados, incorporando testes de segurança e padrões estruturados na fábrica de software.",
            deliverables: [
                "Esteiras automatizadas de Build, Test e Deploy para as principais aplicações",
                "Padrão obrigatório de Code Review documentado e integrado nas ferramentas de repositório",
                "Segregação clara de ambientes de Desenvolvimento, Homologação e Produção"
            ],
            steps: [
                "Configurar ferramentas de CI/CD (ex: GitHub Actions, GitLab CI) nas aplicações mais ativas",
                "Instaurar bloqueios de branch para exigir validação técnica antes de fusão de código",
                "Configurar servidores de homologação para testes de negócio antes do deploy"
            ]
        },
        10: {
            title: "Governança Cloud & Gestão Financeira (FinOps)",
            priority: "Média",
            horizon: "Médio Prazo",
            timeframe: "6-12 meses",
            impact: "Médio Impacto",
            description: "Mapear dependências em nuvens públicas e prestadores de serviços, implementando uma estrutura de governança focada em monitoramento de custo cloud, SLAs rígidos e conformidade técnica.",
            deliverables: [
                "Mapeamento detalhado e SLAs contratuais de fornecedores críticos de nuvem",
                "Políticas ativas de FinOps com desligamento de recursos ociosos fora do expediente",
                "Matriz de acessos segura baseada em privilégio mínimo em painéis de nuvem"
            ],
            steps: [
                "Auditar o faturamento mensal da nuvem em busca de instâncias ociosas ou superdimensionadas",
                "Padronizar regras de governança e tags de identificação de custos por setor",
                "Revisar contratos chaves de consultoria externa e integradores exigindo SLAs formais"
            ]
        },
        11: {
            title: "Plano Anual de Capacitação e Retenção de TI",
            priority: "Baixa",
            horizon: "Longo Prazo",
            timeframe: "12-24 meses",
            impact: "Médio Impacto",
            description: "Desenvolver competências técnicas e culturais dos colaboradores de tecnologia alinhadas ao roadmap de modernização tecnológica da empresa, incentivando certificações de peso.",
            deliverables: [
                "Cronograma oficial e orçamento aprovado para treinamentos técnicos",
                "Trilhas de carreira em Y (Técnica vs Liderança) desenhadas para a área de TI",
                "Incentivo financeiro para realização de exames de certificações estratégicas (ex: AWS, ISO, ITIL)"
            ],
            steps: [
                "Identificar os maiores gaps técnicos da equipe através de autoavaliação profissional",
                "Estruturar parcerias com plataformas online de educação técnica ou cursos externos corporativos",
                "Redigir política interna de reembolso de exames e capacitações de TI"
            ]
        },
        12: {
            title: "Programa de Adequação Total à LGPD",
            priority: "Alta",
            horizon: "Curto Prazo",
            timeframe: "3-6 meses",
            impact: "Alto Impacto",
            description: "Garantir a total conformidade regulatória técnica e jurídica perante a Lei Geral de Proteção de Dados (LGPD), minimizando drasticamente riscos legais, multas da ANPD e danos à marca.",
            deliverables: [
                "Inventário de Dados Pessoais (Data Mapping) mapeado e atualizado",
                "Políticas de Privacidade atualizadas em portais e canais oficiais corporativos",
                "Nomeação oficial e treinamento para a atuação do Encarregado de Dados (DPO)"
            ],
            steps: [
                "Realizar mapeamento detalhado do fluxo de dados pessoais de clientes e colaboradores",
                "Escrever e aprovar aditivos contratuais de proteção de dados com fornecedores",
                "Divulgar a nova política de privacidade e canal oficial para solicitações de titulares"
            ]
        },
        13: {
            title: "Estruturação do Processo de Gestão de Mudanças",
            priority: "Média",
            horizon: "Médio Prazo",
            timeframe: "6-12 meses",
            impact: "Médio Impacto",
            description: "Implementar um processo estruturado de controle de alterações produtivas (CAB - Comitê de Avaliação de Mudanças) para reduzir incidentes decorrentes de modificações operacionais indesejadas.",
            deliverables: [
                "Fluxo de solicitação, aprovação e deploy de mudanças documentado",
                "Comitê de Avaliação de Mudanças (CAB) funcionando de forma virtual ou síncrona",
                "Ficha padrão de mudança contendo obrigatoriedade de plano de testes e rollback"
            ],
            steps: [
                "Estabelecer regras de janela de manutenção e categorização de mudanças (Padrão, Normal, Emergencial)",
                "Instituir o CAB semanalmente com os principais coordenadores e equipe de infraestrutura",
                "Exigir que toda alteração crítica seja testada previamente e possua passo a passo de desfazer"
            ]
        },
        14: {
            title: "Observabilidade de Sistemas e Monitoramento Ativo",
            priority: "Média",
            horizon: "Médio Prazo",
            timeframe: "6-12 meses",
            impact: "Médio Impacto",
            description: "Substituir o monitoramento de up/down básico por observabilidade completa (logs, métricas, traces), estruturando alarmes preventivos antes que a indisponibilidade impacte o cliente final.",
            deliverables: [
                "Ferramenta de monitoramento integrado de rede, servidores e sistemas ativa",
                "Painéis (Dashboards) centrais de status das aplicações expostos ao time técnico",
                "Alertas e notificações automatizadas integradas a canais de chat (ex: Slack, Teams, Telegram)"
            ],
            steps: [
                "Escolher uma ferramenta consolidada de observabilidade (ex: Grafana, Zabbix, Prometheus)",
                "Instalar agentes nos servidores críticos para coleta de memória, CPU e espaço em disco",
                "Configurar gatilhos inteligentes de alarmes que disparem e alertem a equipe de plantão"
            ]
        },
        15: {
            title: "Inovação Estratégica e Transformação Digital",
            priority: "Média",
            horizon: "Longo Prazo",
            timeframe: "12-24 meses",
            impact: "Alto Impacto",
            description: "Posicionar a equipe de TI como consultoria de inovação para as áreas de negócio, desenhando um roadmap claro para acelerar processos, automação de dados e novas experiências de venda.",
            deliverables: [
                "Roadmap estratégico formal de Transformação Digital para a empresa",
                "Orçamento e tempo protegidos da equipe para focar em experimentos de novas tecnologias (ex: IA)",
                "Mapeamento de melhorias de sistemas internas visando ganho de produtividade corporativo"
            ],
            steps: [
                "Realizar workshops de inovação internos com líderes de vendas, financeiro e logística",
                "Priorizar projetos piloto (PoCs) de novas soluções baseadas em automação e integrações de dados",
                "Avaliar ferramentas no mercado que possam automatizar fluxos repetitivos"
            ]
        },
        16: {
            title: "Estruturação de Governança de Dados e BI",
            priority: "Média",
            horizon: "Longo Prazo",
            timeframe: "12-24 meses",
            impact: "Alto Impacto",
            description: "Eliminar a dispersão de dados e planilhas paralelas através de uma estrutura unificada de Business Intelligence, promovendo decisões corporativas rápidas e orientadas a dados.",
            deliverables: [
                "Data Warehouse ou repositório unificado modelado com dados consolidados",
                "Painéis interativos de Business Intelligence (BI) para diretoria e gerência operacional",
                "Políticas formalizadas de qualidade, segurança e regras de negócio dos dados da empresa"
            ],
            steps: [
                "Definir a plataforma oficial de BI (ex: Power BI, Metabase, Looker Studio)",
                "Modelar as bases centrais unificadas de dados oriundas do ERP corporativo e CRM",
                "Treinar lideranças para consumir e interpretar relatórios e decisões baseados em dashboards"
            ]
        },
        17: {
            title: "Planejamento e Gestão Orçamentária de TI (FinOps)",
            priority: "Média",
            horizon: "Longo Prazo",
            timeframe: "12-24 meses",
            impact: "Médio Impacto",
            description: "Implementar uma gestão financeira de tecnologia moderna e transparente, com controle estrito de OPEX/CAPEX, mensurando ROI e custo total de propriedade por serviço de negócio.",
            deliverables: [
                "Painel orçamentário anual de TI com divisões claras de centros de custos",
                "Processo formalizado de estudo de viabilidade financeira (ROI) para projetos tecnológicos",
                "Métricas de FinOps de custo operacional de TI atreladas à performance de faturamento"
            ],
            steps: [
                "Categorizar custos de hardware, software, equipe e nuvens para criar a matriz orçamentária",
                "Estabelecer limites trimestrais para gastos variáveis técnicos",
                "Desenhar formulário básico de justificativa financeira de retorno de investimentos em projetos chaves"
            ]
        },
        18: {
            title: "Modernização das Ferramentas do Colaborador (DEX)",
            priority: "Baixa",
            horizon: "Médio Prazo",
            timeframe: "6-12 meses",
            impact: "Médio Impacto",
            description: "Investir na experiência de trabalho digital do colaborador interno, disponibilizando equipamentos modernos, rede de alta performance e ferramentas eficientes de colaboração.",
            deliverables: [
                "Avaliação semestral interna de satisfação tecnológica (eNPS da TI)",
                "Cronograma transparente de renovação e substituição de computadores corporativos obsoletos",
                "Ambiente de intranet ou ferramenta centralizada moderna de comunicação corporativa"
            ],
            steps: [
                "Rodar pesquisa rápida e simples (3 perguntas) com colaboradores sobre lentidão de sistemas e máquinas",
                "Desenhar política financeira de reposição de computadores por tempo de uso (ex: a cada 3 ou 4 anos)",
                "Substituir sistemas internos de comunicação fragmentados por ferramentas padrão integradas"
            ]
        },
        19: {
            title: "Arquitetura Tecnológica Orientada a APIs",
            priority: "Média",
            horizon: "Longo Prazo",
            timeframe: "12-24 meses",
            impact: "Alto Impacto",
            description: "Desenhar a arquitetura futura corporativa focada em microsserviços, desacoplamento de legados, integrações seguras via APIs públicas/privadas e governança de arquitetura.",
            deliverables: [
                "Documento técnico descrevendo a Arquitetura de Referência futura corporativa",
                "Catalogação completa e documentação ativa de APIs existentes no negócio",
                "Plano de transição gradativa dos sistemas legados instáveis para soluções escaláveis"
            ],
            steps: [
                "Documentar todas as integrações de banco de dados e sistemas chaves existentes",
                "Definir padrões de desenvolvimento baseados em REST APIs seguras e autenticadas",
                "Priorizar a migração ou substituição dos legados de maior custo de manutenção operacional"
            ]
        },
        20: {
            title: "Implementação de Cultura Ágil e Postmortems de TI",
            priority: "Média",
            horizon: "Longo Prazo",
            timeframe: "12-24 meses",
            impact: "Médio Impacto",
            description: "Construir uma cultura operacional de alta performance e transparência, implementando rituais ágeis de acompanhamento e investigações pós-incidente sistêmicas sem atribuição de culpa corporativa.",
            deliverables: [
                "Processo institucionalizado de análise pós-incidente (Postmortem Sem Culpa)",
                "Métricas de performance de equipe compartilhadas de forma transparente",
                "Adoção de rituais ágeis (ex: Standups curtos, retrospectivas) nos times técnicos"
            ],
            steps: [
                "Elaborar o modelo padrão de documento Postmortem focado na falha do processo e não no indivíduo",
                "Estabelecer reuniões quinzenais rápidas com o time para debater melhorias contínuas de deploys",
                "Promover feedbacks transparentes baseados em ownership das entregas de engenharia"
            ]
        }
    };

    return Object.entries(initDefinitions).map(([id, def]) => {
        const q = props.questionScores[parseInt(id) - 1];
        if (!q) return null;

        return {
            id: parseInt(id),
            title: def.title,
            priority: def.priority,
            horizon: def.horizon,
            timeframe: def.timeframe,
            impact: def.impact,
            description: def.description,
            deliverables: def.deliverables,
            steps: def.steps,
            score: q.weight,
            questionName: q.question,
            selectedOption: q.selected_option
        };
    }).filter(init => init.score < 4);
});

// Filtros para o roadmap de iniciativas
const filteredInitiatives = computed(() => {
    return initiatives.value.filter(init => {
        const matchesHorizon = selectedHorizonFilter.value === 'all' || 
            (selectedHorizonFilter.value === 'curto' && init.horizon === 'Curto Prazo') ||
            (selectedHorizonFilter.value === 'medio' && init.horizon === 'Médio Prazo') ||
            (selectedHorizonFilter.value === 'longo' && init.horizon === 'Longo Prazo');
            
        const matchesPriority = selectedPriorityFilter.value === 'all' ||
            (selectedPriorityFilter.value === 'critica' && init.priority === 'Crítica') ||
            (selectedPriorityFilter.value === 'alta' && init.priority === 'Alta') ||
            (selectedPriorityFilter.value === 'media' && init.priority === 'Média') ||
            (selectedPriorityFilter.value === 'baixa' && init.priority === 'Baixa');
            
        return matchesHorizon && matchesPriority;
    });
});

// 5. KPIs recomendados
const recommendedKpis = computed(() => {
    const kpiDefinitions = {
        'Estratégia e Alinhamento': [
            { name: "Alinhamento do PETI", metric: "% de projetos realizados que estavam previstos no PETI", target: "> 85%", freq: "Semestral" },
            { name: "Aderência Orçamentária", metric: "Variação entre o orçamento de TI projetado vs executado", target: "< ±5%", freq: "Mensal" },
            { name: "Retorno de Investimento (ROI)", metric: "% de grandes projetos de TI com ROI avaliado pós-implantação", target: "100%", freq: "Anual" }
        ],
        'Segurança, Riscos e Resiliência': [
            { name: "Adoção de MFA", metric: "% de contas corporativas críticas com MFA obrigatoriamente ativado", target: "100%", freq: "Mensal" },
            { name: "Tempo de Restauração (RTO Real)", metric: "Tempo de restauração em testes de restore vs RTO acordado", target: "100% dentro do RTO", freq: "Trimestral" },
            { name: "Conformidade LGPD", metric: "% de processos de dados mapeados e conformes no ROPD", target: "100%", freq: "Semestral" }
        ],
        'Gestão de Serviços e Operações': [
            { name: "Resolução no Primeiro Contato (FCR)", metric: "% de chamados de TI resolvidos no primeiro nível", target: "> 70%", freq: "Mensal" },
            { name: "Aderência de SLA", metric: "% de chamados resolvidos dentro do prazo acordado", target: "> 92%", freq: "Mensal" },
            { name: "Disponibilidade de Sistemas", metric: "% de uptime dos principais sistemas e ERP corporativo", target: "> 99.7%", freq: "Mensal" }
        ],
        'Tecnologia, Processos e Pessoas': [
            { name: "Taxa de Sucesso de Deploy", metric: "% de alterações promovidas em produção sem incidentes nas 48h seguintes", target: "> 95%", freq: "Mensal" },
            { name: "Rotatividade de TI (Turnover)", metric: "Índice anual de saída de talentos na área técnica", target: "< 15% ao ano", freq: "Anual" },
            { name: "Horas de Treinamento", metric: "Média de horas dedicadas à capacitação técnica anual por colaborador de TI", target: "> 40h por ano", freq: "Anual" }
        ]
    };

    const list = [];
    dimensions.value.forEach(dim => {
        if (dim.percentage < 80) {
            const defs = kpiDefinitions[dim.name] || [];
            defs.forEach(kpi => {
                list.push({ ...kpi, dimension: dim.name, color: dim.color });
            });
        }
    });

    if (list.length === 0) {
        Object.entries(kpiDefinitions).forEach(([dimName, defs]) => {
            const dim = dimensions.value.find(d => d.name === dimName);
            list.push({ ...defs[0], dimension: dimName, color: dim ? dim.color : '#10b981' });
        });
    }

    return list;
});

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head title="Relatório de Maturidade" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('assessments.index')" class="text-gray-400 hover:text-white transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </Link>
                <h1 class="text-xl font-bold text-white">Relatório de Maturidade</h1>
            </div>
        </template>

        <!-- Header Card -->
        <div class="bg-gradient-to-r from-gray-800/80 to-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6 mb-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-white">{{ assessment.company_name }}</h2>
                    <div class="flex items-center gap-4 mt-2 text-sm text-gray-400">
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">person</span>
                            {{ assessment.user_name }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">calendar_today</span>
                            {{ assessment.completed_at }}
                        </span>
                    </div>
                </div>
                <button
                    @click="printReport"
                    class="no-print flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm transition-all shadow-lg shadow-indigo-600/20 active:scale-95"
                >
                    <span class="material-symbols-outlined text-base">print</span>
                    Exportar PDF / Imprimir
                </button>
            </div>
        </div>

        <!-- Main Navigation Tabs -->
        <div class="flex border-b border-gray-700/50 mb-6 gap-2 no-print">
            <button
                @click="activeTab = 'maturity'"
                :class="[
                    'px-4 py-2.5 font-medium text-sm border-b-2 transition-all flex items-center gap-2',
                    activeTab === 'maturity'
                        ? 'border-indigo-500 text-white bg-indigo-500/10'
                        : 'border-transparent text-gray-400 hover:text-gray-200'
                ]"
            >
                <span class="material-symbols-outlined text-sm">analytics</span>
                Diagnóstico de Maturidade
            </button>
            <button
                @click="activeTab = 'pdti'"
                :class="[
                    'px-4 py-2.5 font-medium text-sm border-b-2 transition-all flex items-center gap-2',
                    activeTab === 'pdti'
                        ? 'border-indigo-500 text-white bg-indigo-500/10'
                        : 'border-transparent text-gray-400 hover:text-gray-200'
                ]"
            >
                <span class="material-symbols-outlined text-sm">description</span>
                Plano Diretor de TI (PDTI)
            </button>
        </div>

        <div class="tab-content-maturity" v-show="activeTab === 'maturity'">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Gauge Chart -->
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-white mb-2">
                    <span class="material-symbols-outlined text-indigo-400 mr-2 align-middle">speed</span>
                    Nota Final
                </h3>
                <p class="text-sm text-gray-400 mb-4">Pontuação de {{ assessment.total_score }} de {{ maxScore }} pontos possíveis</p>
                <apexchart
                    type="radialBar"
                    height="350"
                    :options="gaugeOptions"
                    :series="gaugeSeries"
                />
            </div>

            <!-- Maturity Level Card -->
            <div class="flex flex-col gap-6">
                <!-- Current Level -->
                <div
                    class="bg-gray-800/50 backdrop-blur-sm border-2 rounded-2xl p-6 flex-1"
                    :style="{ borderColor: maturityLevel?.color + '40' }"
                >
                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center"
                            :style="{ backgroundColor: maturityLevel?.color + '20' }"
                        >
                            <span class="material-symbols-outlined text-2xl" :style="{ color: maturityLevel?.color }">
                                {{ scorePct >= 80 ? 'verified' : scorePct >= 60 ? 'trending_up' : scorePct >= 40 ? 'trending_flat' : 'warning' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Nível de Maturidade</p>
                            <h3 class="text-2xl font-bold" :style="{ color: maturityLevel?.color }">
                                {{ maturityLevel?.name || 'N/A' }}
                            </h3>
                        </div>
                    </div>
                    <p class="text-sm text-gray-300 leading-relaxed">
                        {{ maturityLevel?.description }}
                    </p>
                    <div class="mt-4 pt-4 border-t border-gray-700/50">
                        <p class="text-xs text-gray-500">
                            Faixa: {{ Math.round((maturityLevel?.min_score / 100) * maxScore) }} a {{ Math.round((maturityLevel?.max_score / 100) * maxScore) }} pontos
                        </p>
                    </div>
                </div>

                <!-- Improvements Section -->
                <div v-if="maturityLevel?.improvements" class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">
                        <span class="material-symbols-outlined text-amber-400 mr-2 align-middle">lightbulb</span>
                        Sugestões de Melhoria
                    </h3>
                    <div class="space-y-2 text-sm text-gray-300 leading-relaxed whitespace-pre-line">
                        {{ maturityLevel.improvements }}
                    </div>
                </div>

                <!-- All Levels Scale -->
                <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
                    <h4 class="text-sm font-semibold text-gray-300 mb-3">Escala de Maturidade</h4>
                    <div class="space-y-2">
                        <div
                            v-for="level in allLevels"
                            :key="level.name"
                            :class="[
                                'flex items-center justify-between px-3 py-2 rounded-lg transition-all',
                                maturityLevel?.name === level.name ? 'bg-gray-700/50 ring-1' : 'opacity-60',
                            ]"
                            :style="maturityLevel?.name === level.name ? { ringColor: level.color + '60' } : {}"
                        >
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: level.color }"></div>
                                <span class="text-sm text-gray-200 font-medium">{{ level.name }}</span>
                                <span v-if="maturityLevel?.name === level.name" class="material-symbols-outlined text-sm" :style="{ color: level.color }">arrow_back</span>
                            </div>
                            <span class="text-xs text-gray-500 font-mono">
                                {{ Math.round((level.min_score / 100) * maxScore) }}-{{ Math.round((level.max_score / 100) * maxScore) }} pts
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bar Chart: Score by Question -->
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6 mb-6">
            <h3 class="text-lg font-semibold text-white mb-4">
                <span class="material-symbols-outlined text-indigo-400 mr-2 align-middle">bar_chart</span>
                Pontuação por Questão
            </h3>
            <apexchart
                type="bar"
                height="400"
                :options="barOptions"
                :series="barSeries"
            />
        </div>

        <!-- Detailed Answers -->
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6 mb-6">
            <h3 class="text-lg font-semibold text-white mb-4">
                <span class="material-symbols-outlined text-indigo-400 mr-2 align-middle">fact_check</span>
                Detalhamento das Respostas
            </h3>
            <div class="space-y-3">
                <div
                    v-for="(q, index) in questionScores"
                    :key="index"
                    class="bg-gray-900/30 border border-gray-700/30 rounded-xl p-4"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3 flex-1">
                            <div class="w-7 h-7 rounded-lg bg-gray-700/50 flex items-center justify-center text-gray-400 text-xs font-bold flex-shrink-0">
                                {{ index + 1 }}
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-200">{{ q.question }}</h4>
                                <p class="text-xs text-gray-500 mt-1">
                                    <span class="material-symbols-outlined text-xs align-middle mr-0.5">check_circle</span>
                                    {{ q.selected_option }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                            <div class="flex gap-0.5">
                                <div
                                    v-for="i in q.max_weight"
                                    :key="i"
                                    :class="[
                                        'w-3 h-3 rounded-sm transition-all',
                                        i <= q.weight ? 'bg-indigo-500' : 'bg-gray-700',
                                    ]"
                                />
                            </div>
                            <span class="text-sm font-bold text-gray-200 ml-1">{{ q.weight }}/{{ q.max_weight }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TI Maturity Indicators -->
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">
                <span class="material-symbols-outlined text-indigo-400 mr-2 align-middle">info</span>
                Indicadores de Nível de Maturidade da TI
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="indicator in indicators"
                    :key="indicator.name"
                    class="p-4 rounded-xl bg-gray-900/30 border border-gray-700/30"
                >
                    <span class="text-sm font-medium text-indigo-300 block mb-1">{{ indicator.name }}</span>
                    <p v-if="indicator.description" class="text-xs text-gray-500 leading-relaxed">
                        {{ indicator.description }}
                    </p>
                </div>
            </div>
        </div>
        </div> <!-- FIM tab-content-maturity -->

        <!-- Plano Diretor de TI (PDTI) -->
        <div class="tab-content-pdti" v-show="activeTab === 'pdti'">
            <!-- PDTI Sub Tabs -->
            <div class="flex flex-wrap gap-2 border-b border-gray-700/30 mb-6 no-print">
                <button
                    @click="activePdtiSubTab = 'dimensions'"
                    :class="[
                        'px-4 py-2 text-sm font-medium rounded-t-lg transition-all flex items-center gap-1.5 border-b-2',
                        activePdtiSubTab === 'dimensions'
                            ? 'border-indigo-500 text-white bg-indigo-500/10'
                            : 'border-transparent text-gray-400 hover:text-gray-200'
                    ]"
                >
                    <span class="material-symbols-outlined text-sm">grid_view</span>
                    Dimensões de Governança
                </button>
                <button
                    @click="activePdtiSubTab = 'risks'"
                    :class="[
                        'px-4 py-2 text-sm font-medium rounded-t-lg transition-all flex items-center gap-1.5 border-b-2',
                        activePdtiSubTab === 'risks'
                            ? 'border-indigo-500 text-white bg-indigo-500/10'
                            : 'border-transparent text-gray-400 hover:text-gray-200'
                    ]"
                >
                    <span class="material-symbols-outlined text-sm">grid_on</span>
                    Matriz de Riscos 5x5
                </button>
                <button
                    @click="activePdtiSubTab = 'roadmap'"
                    :class="[
                        'px-4 py-2 text-sm font-medium rounded-t-lg transition-all flex items-center gap-1.5 border-b-2',
                        activePdtiSubTab === 'roadmap'
                            ? 'border-indigo-500 text-white bg-indigo-500/10'
                            : 'border-transparent text-gray-400 hover:text-gray-200'
                    ]"
                >
                    <span class="material-symbols-outlined text-sm">timeline</span>
                    Roadmap de Iniciativas
                </button>
                <button
                    @click="activePdtiSubTab = 'kpis'"
                    :class="[
                        'px-4 py-2 text-sm font-medium rounded-t-lg transition-all flex items-center gap-1.5 border-b-2',
                        activePdtiSubTab === 'kpis'
                            ? 'border-indigo-500 text-white bg-indigo-500/10'
                            : 'border-transparent text-gray-400 hover:text-gray-200'
                    ]"
                >
                    <span class="material-symbols-outlined text-sm">assignment_turned_in</span>
                    Plano de KPIs
                </button>
            </div>

            <!-- SUB TAB 1: DIMENSÕES DE GOVERNANÇA -->
            <div v-show="activePdtiSubTab === 'dimensions'" class="space-y-6 tab-section-dimensions">
                <div class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6 no-print">
                    <h3 class="text-lg font-bold text-white mb-2">Desempenho por Dimensão de Governança</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Abaixo apresentamos as pontuações consolidadas divididas nas quatro principais áreas de liderança de TI.
                        As dimensões com pontuações abaixo de 80% são consideradas focos de intervenção prioritária.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div
                        v-for="dim in dimensions"
                        :key="dim.name"
                        class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6 flex flex-col justify-between transition-all hover:border-gray-600/70"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-4 mb-4">
                                <h4 class="text-lg font-bold text-white">{{ dim.name }}</h4>
                                <span
                                    class="text-xs font-semibold px-2.5 py-1 rounded-full uppercase tracking-wider flex items-center gap-1"
                                    :style="{ backgroundColor: dim.bgLight, color: dim.color }"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: dim.color }"></span>
                                    {{ dim.status }}
                                </span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mb-5">
                                <div class="flex items-center justify-between text-xs text-gray-400 mb-1.5">
                                    <span>Nível de Maturidade</span>
                                    <span class="font-bold text-white">{{ dim.percentage }}%</span>
                                </div>
                                <div class="w-full bg-gray-700 h-2.5 rounded-full overflow-hidden">
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        :style="{ width: `${dim.percentage}%`, backgroundColor: dim.color }"
                                    ></div>
                                </div>
                                <div class="flex justify-between text-[10px] text-gray-500 mt-1">
                                    <span>Pontuação: {{ dim.score }} / {{ dim.maxScore }} pts</span>
                                    <span v-if="dim.percentage < 80" class="text-orange-400 font-medium">Requer atenção</span>
                                </div>
                            </div>
                        </div>

                        <!-- Associated Gaps -->
                        <div class="pt-4 border-t border-gray-700/50">
                            <h5 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">
                                Lacunas Identificadas (Gaps)
                            </h5>
                            <div v-if="dim.gaps.length > 0" class="space-y-2">
                                <div
                                    v-for="(gap, gIdx) in dim.gaps"
                                    :key="gIdx"
                                    class="text-xs p-2.5 rounded-lg bg-gray-900/30 border border-gray-800 flex items-start gap-2.5"
                                >
                                    <span class="material-symbols-outlined text-amber-500 text-sm mt-0.5">warning</span>
                                    <div>
                                        <p class="font-medium text-gray-300 leading-relaxed">{{ gap.title }}</p>
                                        <p class="text-gray-500 mt-0.5">Status Atual: <strong>{{ gap.selected }}</strong> (Peso: {{ gap.weight }})</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-xs text-emerald-400 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm">check_circle</span>
                                Excelente maturidade nesta dimensão! Nenhum gap crítico identificado.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUB TAB 2: MATRIZ DE RISCOS 5x5 -->
            <div v-show="activePdtiSubTab === 'risks'" class="space-y-6 tab-section-risks">
                <div class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6 no-print">
                    <h3 class="text-lg font-bold text-white mb-2">Matriz de Riscos Interativa 5x5 (Impacto × Probabilidade)</h3>
                    <p class="text-sm text-gray-400 leading-relaxed mb-4">
                        Riscos de TI calculados de forma automática: a probabilidade é proporcional ao gap de conformidade da questão 
                        (notas menores elevam a probabilidade de falha operacional).
                    </p>
                    <div class="flex items-center gap-4 text-xs">
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-[#ef4444]"></span> Crítico (&ge; 20)</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-[#f97316]"></span> Alto (&ge; 12)</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-[#eab308]"></span> Médio (&ge; 8)</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-[#10b981]"></span> Baixo (&lt; 8)</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                    <!-- MATRIX GRID (col-span-5) -->
                    <div class="xl:col-span-5 bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
                        <h4 class="text-sm font-bold text-gray-300 mb-6 text-center">Mapa de Calor da TI</h4>
                        
                        <div class="flex flex-col items-center">
                            <!-- Matrix Body -->
                            <div class="w-full max-w-[340px]">
                                <!-- Rows Loop (Impact 5 down to 1) -->
                                <div 
                                    v-for="(row, rIdx) in riskMatrixGrid" 
                                    :key="rIdx" 
                                    class="flex items-center h-12"
                                >
                                    <!-- Y-Axis Label (Impact Label) -->
                                    <div class="w-8 flex items-center justify-center text-xs font-mono text-gray-500 font-bold">
                                        I{{ 5 - rIdx }}
                                    </div>
                                    
                                    <!-- Row Cells -->
                                    <div class="flex-1 grid grid-cols-5 gap-1.5 h-full">
                                        <div
                                            v-for="(cell, cIdx) in row"
                                            :key="cIdx"
                                            @click="selectRiskCell(cell.probability, cell.impact)"
                                            :class="[
                                                'rounded-lg flex flex-col items-center justify-center cursor-pointer transition-all duration-200 select-none relative',
                                                selectedRiskCell && selectedRiskCell.p === cell.probability && selectedRiskCell.i === cell.impact
                                                    ? 'ring-4 ring-white border-2 border-black scale-105 z-10 shadow-xl'
                                                    : 'hover:scale-102 hover:shadow-md'
                                            ]"
                                            :style="{
                                                backgroundColor: cell.impact * cell.probability >= 20
                                                    ? 'rgba(239, 68, 68, 0.85)'
                                                    : cell.impact * cell.probability >= 12
                                                    ? 'rgba(249, 115, 22, 0.85)'
                                                    : cell.impact * cell.probability >= 8
                                                    ? 'rgba(234, 179, 8, 0.85)'
                                                    : 'rgba(16, 185, 129, 0.85)'
                                            }"
                                        >
                                            <span class="text-sm font-bold text-white drop-shadow-sm">{{ cell.count }}</span>
                                            <span class="text-[9px] text-white/70 block font-mono">P{{ cell.probability }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- X-Axis Labels -->
                                <div class="flex items-center mt-2.5">
                                    <div class="w-8"></div>
                                    <div class="flex-1 grid grid-cols-5 gap-1.5 text-center text-xs font-mono text-gray-500 font-bold">
                                        <span>P1</span>
                                        <span>P2</span>
                                        <span>P3</span>
                                        <span>P4</span>
                                        <span>P5</span>
                                    </div>
                                </div>
                            </div>

                            <p class="text-[10px] text-gray-400 mt-6 text-center italic">
                                * P = Probabilidade, I = Impacto. Clique em qualquer quadrante para filtrar os riscos abaixo.
                            </p>
                        </div>
                    </div>

                    <!-- RISK TABLE (col-span-7) -->
                    <div class="xl:col-span-7 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-700/50 pb-2.5">
                            <h4 class="text-base font-bold text-white">
                                Detalhamento dos Riscos de TI
                                <span class="text-xs text-gray-400 font-normal">({{ filteredRisks.length }} riscos)</span>
                            </h4>
                            
                            <div v-if="selectedRiskCell" class="flex items-center gap-2">
                                <span class="text-xs font-medium px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300">
                                    Filtrado: P{{ selectedRiskCell.p }} × I{{ selectedRiskCell.i }}
                                </span>
                                <button 
                                    @click="selectedRiskCell = null" 
                                    class="text-xs text-gray-400 hover:text-white transition-colors flex items-center gap-0.5"
                                >
                                    <span class="material-symbols-outlined text-[14px]">close</span> Limpar
                                </button>
                            </div>
                        </div>

                        <div class="max-h-[500px] overflow-y-auto pr-1 space-y-3">
                            <div
                                v-for="risk in filteredRisks"
                                :key="risk.id"
                                class="bg-gray-800/40 backdrop-blur-sm border-l-4 rounded-xl p-4 transition-all duration-200 hover:bg-gray-800/60"
                                :style="{ borderLeftColor: risk.color }"
                            >
                                <div class="flex items-start justify-between gap-4 mb-2">
                                    <div>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">
                                            Risco {{ risk.id }} &bull; {{ risk.category }}
                                        </span>
                                        <h5 class="text-sm font-semibold text-white leading-relaxed">{{ risk.risk }}</h5>
                                    </div>
                                    <span 
                                        class="text-xs font-bold px-2 py-0.5 rounded"
                                        :style="{ backgroundColor: risk.bgLight, color: risk.color }"
                                    >
                                        {{ risk.level }} ({{ risk.score }})
                                    </span>
                                </div>

                                <div class="grid grid-cols-3 gap-2 py-2 border-t border-b border-gray-700/20 my-3 text-center text-xs">
                                    <div>
                                        <p class="text-gray-500 text-[10px]">Probabilidade</p>
                                        <p class="font-bold text-white">{{ risk.probability }} / 5</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 text-[10px]">Impacto</p>
                                        <p class="font-bold text-white">{{ risk.impact }} / 5</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 text-[10px]">Nota de Risco</p>
                                        <p class="font-bold text-white">{{ risk.score }} / 25</p>
                                    </div>
                                </div>

                                <div class="text-xs space-y-1.5 text-gray-300">
                                    <p><strong class="text-gray-500">Controle Avaliado:</strong> {{ risk.question }}</p>
                                    <p><strong class="text-gray-500">Status no Negócio:</strong> {{ risk.currentControl }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUB TAB 3: ROADMAP DE INICIATIVAS -->
            <div v-show="activePdtiSubTab === 'roadmap'" class="space-y-6 tab-section-roadmap">
                <div class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6 no-print">
                    <h3 class="text-lg font-bold text-white mb-2">Roadmap Estratégico de Iniciativas</h3>
                    <p class="text-sm text-gray-400 leading-relaxed mb-4">
                        Iniciativas de mitigação de gaps priorizadas estrategicamente. Filtre por prioridade ou prazo de implantação.
                    </p>

                    <!-- Roadmap Filters -->
                    <div class="flex flex-wrap gap-4 items-center">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-gray-500 uppercase">Prazo:</span>
                            <div class="flex bg-gray-900 rounded-lg p-0.5 border border-gray-800">
                                <button
                                    v-for="f in [{id:'all', label:'Todos'}, {id:'curto', label:'Curto (0-6m)'}, {id:'medio', label:'Médio (6-12m)'}, {id:'longo', label:'Longo (12-24m)'}]"
                                    :key="f.id"
                                    @click="selectedHorizonFilter = f.id"
                                    :class="[
                                        'px-2.5 py-1 text-xs rounded-md transition-all',
                                        selectedHorizonFilter === f.id ? 'bg-indigo-600 text-white font-medium' : 'text-gray-400 hover:text-gray-200'
                                    ]"
                                >
                                    {{ f.label }}
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-gray-500 uppercase">Prioridade:</span>
                            <div class="flex bg-gray-900 rounded-lg p-0.5 border border-gray-800">
                                <button
                                    v-for="p in [{id:'all', label:'Todas'}, {id:'critica', label:'Crítica'}, {id:'alta', label:'Alta'}, {id:'media', label:'Média'}, {id:'baixa', label:'Baixa'}]"
                                    :key="p.id"
                                    @click="selectedPriorityFilter = p.id"
                                    :class="[
                                        'px-2.5 py-1 text-xs rounded-md transition-all',
                                        selectedPriorityFilter === p.id ? 'bg-indigo-600 text-white font-medium' : 'text-gray-400 hover:text-gray-200'
                                    ]"
                                >
                                    {{ p.label }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Initiatives Accordion Grid -->
                <div v-if="filteredInitiatives.length > 0" class="space-y-4">
                    <div
                        v-for="init in filteredInitiatives"
                        :key="init.id"
                        class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl overflow-hidden transition-all duration-200 hover:border-gray-600/70"
                    >
                        <!-- Accordion Header -->
                        <div
                            @click="toggleInitiative(init.id)"
                            class="p-5 flex items-center justify-between cursor-pointer select-none transition-colors hover:bg-gray-700/20"
                        >
                            <div class="flex-1 pr-4">
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <span
                                        class="text-[10px] font-bold px-2 py-0.5 rounded tracking-wider uppercase"
                                        :class="{
                                            'bg-red-500/10 text-red-400 border border-red-500/20': init.priority === 'Crítica',
                                            'bg-orange-500/10 text-orange-400 border border-orange-500/20': init.priority === 'Alta',
                                            'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20': init.priority === 'Média',
                                            'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20': init.priority === 'Baixa'
                                        }"
                                    >
                                        {{ init.priority }}
                                    </span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded tracking-wider uppercase bg-gray-700/30 text-gray-300 border border-gray-700/40 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[10px]">schedule</span>
                                        {{ init.horizon }} ({{ init.timeframe }})
                                    </span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded tracking-wider uppercase bg-gray-700/30 text-indigo-400 border border-indigo-500/20">
                                        {{ init.impact }}
                                    </span>
                                </div>
                                <h4 class="text-base font-bold text-white leading-snug">{{ init.title }}</h4>
                            </div>
                            <span 
                                class="material-symbols-outlined text-gray-400 transition-transform duration-300"
                                :class="{ 'rotate-180 text-white': expandedInitiativeId === init.id }"
                            >
                                keyboard_arrow_down
                            </span>
                        </div>

                        <!-- Accordion Body -->
                        <div
                            v-show="expandedInitiativeId === init.id"
                            class="p-5 border-t border-gray-700/30 bg-gray-900/10"
                        >
                            <p class="text-sm text-gray-300 leading-relaxed mb-5">{{ init.description }}</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Deliverables -->
                                <div>
                                    <h5 class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-3">
                                        Entregáveis Principais
                                    </h5>
                                    <ul class="space-y-2.5">
                                        <li
                                            v-for="(del, dIdx) in init.deliverables"
                                            :key="dIdx"
                                            class="text-xs text-gray-300 flex items-start gap-2"
                                        >
                                            <span class="material-symbols-outlined text-indigo-400 text-sm select-none">check_box</span>
                                            <span class="leading-relaxed">{{ del }}</span>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Action Steps -->
                                <div>
                                    <h5 class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-3">
                                        Passo a Passo de Implantação
                                    </h5>
                                    <ol class="space-y-2.5">
                                        <li
                                            v-for="(step, sIdx) in init.steps"
                                            :key="sIdx"
                                            class="text-xs text-gray-300 flex gap-2.5 items-start"
                                        >
                                            <span class="w-5 h-5 rounded bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-[10px] font-bold text-indigo-300 flex-shrink-0">
                                                {{ sIdx + 1 }}
                                            </span>
                                            <span class="leading-relaxed">{{ step }}</span>
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-else class="bg-gray-800/30 rounded-2xl border border-gray-700/30 p-8 text-center">
                    <span class="material-symbols-outlined text-4xl text-emerald-400 mb-3">verified</span>
                    <h4 class="text-base font-bold text-white mb-1">Nenhuma iniciativa pendente!</h4>
                    <p class="text-xs text-gray-400 max-w-md mx-auto">
                        Parabéns! Sua organização atingiu maturidade excelente em todos os controles filtrados. Nenhuma ação corretiva é recomendada.
                    </p>
                </div>
            </div>

            <!-- SUB TAB 4: PLANO DE KPIS -->
            <div v-show="activePdtiSubTab === 'kpis'" class="space-y-6 tab-section-kpis">
                <div class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6 no-print">
                    <h3 class="text-lg font-bold text-white mb-2">Plano Diretor de Indicadores (KPIs)</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Indicadores de performance sugeridos especificamente com foco na melhoria das dimensões com menor pontuação de maturidade tecnológica.
                    </p>
                </div>

                <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-900/35 border-b border-gray-700 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    <th class="p-4">Dimensão</th>
                                    <th class="p-4">Indicador</th>
                                    <th class="p-4">Métrica</th>
                                    <th class="p-4">Meta Recomendada</th>
                                    <th class="p-4">Frequência</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/30 text-xs text-gray-300">
                                <tr
                                    v-for="(kpi, kIdx) in recommendedKpis"
                                    :key="kIdx"
                                    class="hover:bg-gray-700/10 transition-colors"
                                >
                                    <td class="p-4">
                                        <span 
                                            class="inline-block px-2.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider"
                                            :style="{ backgroundColor: kpi.color + '20', color: kpi.color }"
                                        >
                                            {{ kpi.dimension }}
                                        </span>
                                    </td>
                                    <td class="p-4 font-bold text-white">{{ kpi.name }}</td>
                                    <td class="p-4 leading-relaxed">{{ kpi.metric }}</td>
                                    <td class="p-4 font-mono text-emerald-400 font-semibold">{{ kpi.target }}</td>
                                    <td class="p-4">
                                        <span class="px-2 py-0.5 rounded bg-gray-700/40 text-gray-400 font-medium">
                                            {{ kpi.freq }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Estilos globais e de animações interativas */
.hover\:scale-102:hover {
    transform: scale(1.02);
}

/* --- ESTILOS DE IMPRESSÃO CORPORATIVOS --- */
@media print {
    /* Esconder o layout padrão e menus */
    .no-print,
    header,
    nav,
    aside,
    footer,
    button,
    .main-nav-tabs,
    .sub-tabs-container,
    .route-link-back {
        display: none !important;
    }

    /* Resetar fundos escuros do layout padrão */
    body,
    html,
    main,
    #app {
        background-color: #ffffff !important;
        color: #0f172a !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Mostrar simultaneamente maturidade e PDTI no PDF */
    .tab-content-maturity,
    .tab-content-pdti {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
        background-color: transparent !important;
    }

    /* Mostrar todas as sub-seções do PDTI seguidas */
    .tab-section-dimensions,
    .tab-section-risks,
    .tab-section-roadmap,
    .tab-section-kpis {
        display: block !important;
        page-break-before: always !important;
        break-before: page !important;
    }

    /* Otimizar quebra de página de cada card */
    .bg-gray-800\/50,
    .bg-gray-800\/40,
    .bg-gray-900\/30,
    .bg-gradient-to-r,
    .tab-section-roadmap > div,
    .tab-section-dimensions > div,
    .tab-section-kpis > div {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        margin-bottom: 1.5rem !important;
    }

    /* Forçar cores corporativas na impressão */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    /* Estilização para o layout de impressão corporativo */
    .bg-gray-800\/50,
    .bg-gray-800\/40,
    .bg-gray-900\/30,
    .bg-gradient-to-r {
        background: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: none !important;
        border-radius: 12px !important;
        color: #1e293b !important;
    }

    h1, h2, h3, h4, h5, h6 {
        color: #0f172a !important;
    }

    p, li, td, th {
        color: #334155 !important;
    }

    /* Resetar classes de texto cinza para alto contraste e legibilidade em papel */
    .text-gray-300,
    .text-gray-400,
    .text-gray-500,
    .text-gray-600 {
        color: #475569 !important;
    }

    /* Otimizar cores semânticas para contraste na folha de papel */
    .text-indigo-400, .text-indigo-300 {
        color: #4f46e5 !important; /* Indigo mais forte */
    }
    .text-amber-400, .text-amber-500 {
        color: #b45309 !important; /* Amber mais escuro */
    }
    .text-emerald-400, .text-emerald-500 {
        color: #047857 !important; /* Esmeralda mais escuro */
    }
    .text-red-400, .text-red-500 {
        color: #b91c1c !important; /* Vermelho mais escuro */
    }

    /* Garantir renderização dos ícones do Material Symbols */
    .material-symbols-outlined {
        display: inline-block !important;
        font-family: 'Material Symbols Outlined' !important;
        font-style: normal;
        font-weight: normal;
        font-variant: normal;
        text-transform: none;
        line-height: 1;
        -webkit-font-smoothing: antialiased;
        color: inherit !important;
    }

    /* Bordas de tabela e grid em cinza escuro */
    .border-gray-700\/50,
    .border-gray-700\/30,
    .border {
        border-color: #cbd5e1 !important;
    }

    /* Forçar exibição de toda a tabela de detalhamento de riscos (sem limites de scroll) */
    .tab-section-risks .max-h-\[500px\] {
        max-height: none !important;
        overflow: visible !important;
    }

    /* Forçar abertura das UIs colapsáveis do Roadmap */
    .tab-section-roadmap .bg-gray-800\/40 > div:nth-child(2) {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    /* Garantir que o grid interno de entregáveis e passos fique em 2 colunas no papel */
    .tab-section-roadmap .bg-gray-800\/40 > div:nth-child(2) .grid {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 1.5rem !important;
    }

    /* Esconder o ícone de seta de sanfona no papel */
    .tab-section-roadmap .bg-gray-800\/40 > div:first-child .material-symbols-outlined:last-child {
        display: none !important;
    }

    /* Ajuste na visualização do grid de matriz de riscos na folha */
    .xl\:col-span-5 {
        width: 100% !important;
        display: flex !important;
        justify-content: center !important;
        margin-bottom: 2rem !important;
    }
}
</style>

