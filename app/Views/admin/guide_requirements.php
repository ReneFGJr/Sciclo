<?= view('layout/header') ?>
<?= view('layout/navbar') ?>

<?php
$questions = isset($questions) && is_array($questions) ? $questions : [];
$sections = isset($sections) && is_array($sections) ? $sections : [];
?>

<style>
    .guide-shell {
        background: radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 35%),
            radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 30%),
            linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
        min-height: 100vh;
    }

    .guide-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 55%, #38bdf8 100%);
        color: #fff;
        border-radius: 28px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
    }

    .guide-card {
        border: 0;
        border-radius: 24px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .guide-axis-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.1);
        color: #1d4ed8;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .guide-title {
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    .guide-subtitle {
        color: rgba(255, 255, 255, 0.85);
    }

    .guide-item {
        border-left: 4px solid #cbd5e1;
        padding-left: 1rem;
        margin-bottom: 1rem;
    }

    .guide-item.root {
        border-left-color: #2563eb;
        background: rgba(37, 99, 235, 0.04);
        border-radius: 16px;
        padding: 1rem 1rem 1rem 1.1rem;
    }

    .guide-meta {
        font-size: 0.85rem;
        color: #64748b;
    }

    .guide-level {
        font-size: 0.8rem;
        font-weight: 700;
        color: #0f172a;
        background: #e2e8f0;
        border-radius: 999px;
        padding: 0.2rem 0.65rem;
        display: inline-block;
        margin-bottom: 0.55rem;
    }

    .guide-question {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.35rem;
    }
</style>

<div class="guide-shell py-5">
    <div class="container">
        <div class="guide-hero p-4 p-lg-5 mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
                <div>
                    <span class="guide-axis-badge mb-3">Guia de requisitos</span>
                    <h1 class="display-6 guide-title mb-2">Guia de certificação de repositório confiável</h1>
                    <p class="lead guide-subtitle mb-0">O Guia de Certificação de Repositório Confiável reúne princípios, requisitos e boas práticas para avaliar e demonstrar a confiabilidade de repositórios digitais. Seu objetivo é orientar instituições na implementação de políticas, processos e infraestrutura que assegurem a preservação digital, a integridade, a autenticidade, a segurança e o acesso contínuo aos objetos digitais, servindo como referência para certificações nacionais e internacionais de repositórios confiáveis..</p>
                </div>
            </div>
        </div>

        <?php if (!empty($sections)): ?>
            <?php foreach ($sections as $section): ?>
                <div class="card guide-card mb-4">
                    <div class="card-header bg-white border-0 py-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <div>
                            <span class="guide-axis-badge">Eixo <?= esc($section['eixo']) ?></span>
                            <h2 class="h4 mt-2 mb-1 guide-title"><?= nl2br(glossario_conteudo($section['titulo'] !== '' ? $section['titulo'] : 'Título não informado')) ?></h2>
                            <?php if (!empty($section['descricao'])): ?>
                                <p class="mb-0 text-muted"><?= nl2br(glossario_conteudo($section['descricao'])) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($section['feedback'])): ?>
                                <div class="guide-meta mt-2">Feedback</div>
                                <p class="mb-0 text-muted"><?= nl2br(glossario_conteudo($section['feedback'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="text-md-end">
                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis px-3 py-2"><?= count($section['items']) ?> itens</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <?php foreach ($section['items'] as $item): ?>
                            <?php $isRoot = trim((string) ($item['nivel2'] ?? '')) === ''; ?>
                            <?php if ($item['nivel2'] != '') { ?>
                            <div class="guide-item <?= $isRoot ? 'root' : '' ?>">
                                <div class="guide-level"><?= $isRoot ? 'Título do eixo' : 'Critério' ?> <?= esc((string) ($item['nivel1'] ?? '') . '.' . (string) ($item['nivel2'] ?? '') . (trim((string) ($item['nivel3'] ?? '')) !== '' ? '.' . (string) $item['nivel3'] : '')) ?> - <?= nl2br(glossario_conteudo($item['questao'] ?? '')) ?></div>
                                <?php if (!empty($item['descricao'])): ?>
                                    <div class="text-body-secondary"><?= nl2br(glossario_conteudo($item['descricao'])) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['feedback'])): ?>
                                    <div class="guide-meta mt-2">Feedback</div>
                                    <div class="text-body-secondary"><?= nl2br(glossario_conteudo($item['feedback'])) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php } ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-warning">Nenhuma questão encontrada na tabela certificacao_questoes.</div>
        <?php endif; ?>
    </div>
</div>

<?= view('layout/footer') ?>