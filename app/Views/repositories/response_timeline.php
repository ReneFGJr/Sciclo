<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<?php $duration = [\App\Libraries\RepositoryResponseTimeline::class, 'duration']; ?>
<style>
.response-timeline { list-style: none; border-left: 2px solid #cbd5e1; margin-left: .5rem; padding-left: 1.5rem; }
.response-timeline > li { position: relative; padding-bottom: 1.5rem; }
.response-timeline > li::before { content: ''; position: absolute; left: -1.95rem; top: .45rem; width: .75rem; height: .75rem; border-radius: 50%; background: #0d6efd; border: 2px solid #fff; }
</style>
<main class="container py-5">
    <a class="btn btn-outline-secondary mb-4" href="<?= site_url('repositories/' . (int) $repository['id']) ?>">Voltar ao reposit&#243;rio</a>
    <h1 class="fw-bold mb-2">Tempo de preenchimento</h1>
    <p class="lead"><?= esc($repository['repository_name'] ?: $repository['base_url']) ?></p>
    <p class="text-muted">Cronologia do primeiro salvamento de cada resposta. Os intervalos s&#227;o calculados desde a resposta anterior e incluem pausas. N&#227;o representam tempo de trabalho ativo. Respostas autom&#225;ticas e salvamentos em lote podem ter intervalo zero. A primeira resposta n&#227;o entra na m&#233;dia por n&#227;o ter um registro anterior.</p>
    <?php if ($timeline['missing_dates'] || $timeline['after_submission']): ?>
        <p class="small text-muted"><?= (int) $timeline['missing_dates'] ?> respostas sem data v&#225;lida e <?= (int) $timeline['after_submission'] ?> respostas posteriores ao envio foram exclu&#237;das do c&#225;lculo.</p>
    <?php endif; ?>
    <div class="row g-4">
        <section class="col-lg-4" aria-labelledby="group-average-title">
            <div class="card bg-light border-0"><div class="card-body">
                <h2 class="h5 mb-3" id="group-average-title">M&#233;dia por grupo (N&#237;vel 1.2)</h2>
                <?php foreach ($timeline['groups'] as $level => $group): ?>
                    <div class="border-top py-3">
                        <div class="d-flex justify-content-between gap-2"><strong>Grupo <?= esc((string) $level) ?></strong><span><?= esc($group['average_seconds'] !== null ? $duration($group['average_seconds']) : 'Indisponível') ?></span></div>
                        <small class="text-muted"><?= (int) $group['answers'] ?> respostas; <?= (int) $group['intervals'] ?> intervalos na m&#233;dia.</small>
                    </div>
                <?php endforeach; ?>
                <?php if (!$timeline['groups']): ?><p class="text-muted mb-0">Sem dados para calcular as m&#233;dias.</p><?php endif; ?>
            </div></div>
        </section>
        <section class="col-lg-8" aria-labelledby="response-timeline-title">
            <h2 class="h4 mb-4" id="response-timeline-title">Linha do tempo das respostas</h2>
            <?php if (!$timeline['entries']): ?><p class="text-muted">Nenhuma resposta com data dispon&#237;vel.</p><?php endif; ?>
            <ol class="response-timeline">
                <?php foreach ($timeline['entries'] as $entry): ?>
                    <?php $q = $entry['question']; ?>
                    <li>
                        <time class="small text-muted" datetime="<?= esc($entry['at']->format('Y-m-d\TH:i:s'), 'attr') ?>"><?= esc($entry['at']->format('d/m/Y H:i:s')) ?></time>
                        <article class="card mt-2"><div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                                <span class="badge bg-primary">Grupo <?= esc($entry['group']) ?></span>
                                <strong class="small"><?= esc($entry['seconds'] !== null ? $duration($entry['seconds']) . ' desde a resposta anterior' : 'Primeira resposta') ?></strong>
                            </div>
                            <h3 class="h6 mb-2"><?= esc(implode('.', [$q['nivel1'] ?? '', $q['nivel2'] ?? '', $q['nivel3'] ?? ''])) ?> - <?= esc($q['questao'] ?? '') ?></h3>
                            <?php if (!empty($q['descricao'])): ?><p class="small text-muted mb-2"><?= esc(mb_strimwidth(trim(strip_tags($q['descricao'])), 0, 220, '...', 'UTF-8')) ?></p><?php endif; ?>
                            <?php $answer = in_array($q['tipo_resposta'], ['SN', 'SOFTWARE'], true) ? (['1' => 'Sim', '2' => 'Não'][$entry['answer']] ?? $entry['answer']) : $entry['answer']; ?>
                            <p class="small mb-0"><strong>Resposta:</strong> <?= esc($answer) ?></p>
                        </div></article>
                    </li>
                <?php endforeach; ?>
                <?php if ($timeline['submitted_at']): ?>
                    <li>
                        <time class="small text-muted"><?= esc($timeline['submitted_at']->format('d/m/Y H:i:s')) ?></time>
                        <div class="alert alert-success mt-2 mb-0"><strong>Formul&#225;rio enviado</strong><?php if ($timeline['submission_seconds'] !== null): ?><br><?= esc($duration($timeline['submission_seconds'])) ?> desde a &#250;ltima resposta.<?php endif; ?></div>
                    </li>
                <?php endif; ?>
            </ol>
        </section>
    </div>
</main>
<?= view('layout/footer') ?>
