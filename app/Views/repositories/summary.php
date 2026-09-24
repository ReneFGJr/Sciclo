<?php
$categories = [
    'yes' => ['label' => 'Sim', 'color' => 'bg-success'],
    'no' => ['label' => 'Não', 'color' => 'bg-danger'],
    'other' => ['label' => 'Outras respostas', 'color' => 'bg-primary'],
    'pending' => ['label' => 'Sem resposta', 'color' => 'bg-secondary'],
];
$percent = static fn ($value) => number_format($value, 1, ',', '.') . '%';
?>
<h2 class="h4 mb-2">Resumo das respostas</h2>
<p class="text-muted mb-4">Visão das respostas salvas neste repositório. O preenchimento considera todas as questões, exceto blocos informativos, e não representa uma nota de certificação.</p>
<?php if (!$summary['total']): ?>
    <div class="alert alert-info">Nenhuma questão disponível para compor o resumo.</div>
<?php else: ?>
    <div class="row g-3 mb-4">
        <?php foreach (['total' => 'Questões', 'answered' => 'Respondidas', 'pending' => 'Pendentes', 'evidences' => 'Evidências vinculadas'] as $key => $label): ?>
            <div class="col-6 col-xl-3">
                <div class="card shadow-sm h-100"><div class="card-body">
                    <div class="text-muted"><?= esc($label) ?></div>
                    <div class="fs-2 fw-bold"><?= $summary[$key] ?></div>
                </div></div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="card shadow-sm mb-4"><div class="card-body">
        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
            <h3 class="h5 mb-0">Preenchimento geral</h3>
            <strong><?= $percent($summary['completion']) ?> — <?= $summary['answered'] ?> de <?= $summary['total'] ?> questões</strong>
        </div>
        <div class="progress" role="progressbar" aria-label="Preenchimento geral" aria-valuenow="<?= $summary['completion'] ?>" aria-valuemin="0" aria-valuemax="100" style="height: 20px;">
            <div class="progress-bar bg-primary" style="width: <?= $summary['completion'] ?>%"></div>
        </div>
        <p class="text-muted mb-0 mt-3"><?= $summary['withEvidence'] ?> questões com evidências · <?= $summary['comments'] ?> questões com comentários.</p>
    </div></div>
    <div class="row g-4 mb-4">
        <div class="col-lg-5"><section class="card shadow-sm h-100" aria-labelledby="answer-distribution-title"><div class="card-body">
            <h3 class="h5 mb-3" id="answer-distribution-title">Distribuição das respostas</h3>
            <?php foreach ($categories as $key => $category): $ratio = round(100 * $summary[$key] / $summary['total'], 1); ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between gap-2 mb-1"><span><?= esc($category['label']) ?></span><strong><?= $summary[$key] ?> (<?= $percent($ratio) ?>)</strong></div>
                    <div class="progress" aria-hidden="true" style="height: 16px;"><div class="progress-bar <?= $category['color'] ?>" style="width: <?= $ratio ?>%"></div></div>
                </div>
            <?php endforeach; ?>
            <p class="small text-muted mb-0">“Sim” e “Não” correspondem às questões desse tipo. Demais valores preenchidos aparecem em “Outras respostas”. Percentuais sobre o total de questões.</p>
        </div></section></div>
        <div class="col-lg-7"><section class="card shadow-sm h-100" aria-labelledby="level-completion-title"><div class="card-body">
            <h3 class="h5 mb-3" id="level-completion-title">Preenchimento por nível</h3>
            <?php foreach ($summary['groups'] as $level => $group): ?>
                <div class="mb-3">
                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-1"><span>Critérios <?= esc((string) $level) ?></span><strong><?= $group['answered'] ?>/<?= $group['total'] ?> · <?= $percent($group['completion']) ?></strong></div>
                    <div class="progress" role="progressbar" aria-label="<?= esc('Preenchimento dos critérios ' . $level, 'attr') ?>" aria-valuenow="<?= $group['completion'] ?>" aria-valuemin="0" aria-valuemax="100" style="height: 16px;"><div class="progress-bar bg-primary" style="width: <?= $group['completion'] ?>%"></div></div>
                </div>
            <?php endforeach; ?>
        </div></section></div>
    </div>
    <section class="card shadow-sm" aria-labelledby="summary-counts-title"><div class="card-body">
        <h3 class="h5 mb-3" id="summary-counts-title">Quantitativos por nível</h3>
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead><tr><th scope="col">Nível</th><th scope="col">Questões</th><th scope="col">Sim</th><th scope="col">Não</th><th scope="col">Outras</th><th scope="col">Pendentes</th><th scope="col">Evidências</th><th scope="col">Comentários</th></tr></thead>
                <tbody>
                    <?php foreach ($summary['groups'] as $level => $group): ?>
                        <tr><th scope="row"><?= esc((string) $level) ?></th><?php foreach (['total', 'yes', 'no', 'other', 'pending', 'evidences', 'comments'] as $key): ?><td><?= $group[$key] ?></td><?php endforeach; ?></tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot><tr class="fw-bold"><th scope="row">Total</th><?php foreach (['total', 'yes', 'no', 'other', 'pending', 'evidences', 'comments'] as $key): ?><td><?= $summary[$key] ?></td><?php endforeach; ?></tr></tfoot>
            </table>
        </div>
        <p class="small text-muted mt-3 mb-0">Evidências contabilizam vínculos às questões; um mesmo documento pode estar vinculado a mais de uma questão. Comentários contabilizam questões com comentário preenchido.</p>
    </div></section>
<?php endif; ?>
