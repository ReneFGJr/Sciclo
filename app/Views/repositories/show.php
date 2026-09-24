<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<?php
$type = $repository['repository_type'] ?? '';
$types = ['publicacao' => 'Publicação', 'dados' => 'Dados', 'ambos' => 'Publicação e Dados', 'arquivistico' => 'Arquivístico'];
$evaluated = !empty($repository['seal_data_avaliation']) && $repository['seal_data_avaliation'] !== '0000-00-00 00:00:00';
$status = $evaluated ? 'Avaliado' : (!empty($repository['submitted_at']) ? 'Enviado para avaliação' : 'Em processo de submissão');
$fields = [
    'id' => 'Identificador',
    'base_url' => 'URL do repositório',
    'base_url_oai' => 'Endpoint OAI-PMH',
    'admin_email' => 'E-mail de contato',
    'repository_software_version' => 'Versão do software',
    'protocol_version' => 'Versão do protocolo',
    'earliest_datestamp' => 'Registro mais antigo',
    'deleted_record' => 'Política de registros excluídos',
    'granularity' => 'Granularidade',
    'compression' => 'Compressão',
];
?>
<main class="container py-5">
    <a class="btn btn-outline-secondary mb-4" href="<?= base_url('repositories') ?>"><i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar aos repositórios</a>
    <h1 class="fw-bold mb-3"><?= esc($repository['repository_name'] ?: 'Repositório #' . $repository['id']) ?></h1>
    <ul class="nav nav-tabs mb-4" role="tablist" aria-label="Detalhes do repositório">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="repository-data-tab" data-bs-toggle="tab" data-bs-target="#repository-data" type="button" role="tab" aria-controls="repository-data" aria-selected="true">Dados do repositório</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="repository-summary-tab" data-bs-toggle="tab" data-bs-target="#repository-summary" type="button" role="tab" aria-controls="repository-summary" aria-selected="false">Resumo</button>
        </li>
        <?php $tabIndex = 0; foreach ($criteriaGroups as $level => $questions): $tabIndex++; ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="criteria-<?= $tabIndex ?>-tab" data-bs-toggle="tab" data-bs-target="#criteria-<?= $tabIndex ?>" type="button" role="tab" aria-controls="criteria-<?= $tabIndex ?>" aria-selected="false">Critérios <?= esc((string) $level) ?></button>
        </li>
        <?php endforeach; ?>
    </ul>
    <div class="tab-content">
    <div class="tab-pane fade" id="repository-summary" role="tabpanel" aria-labelledby="repository-summary-tab" tabindex="0">
        <?= view('repositories/summary', ['summary' => $summary]) ?>
    </div>
    <div class="tab-pane fade show active" id="repository-data" role="tabpanel" aria-labelledby="repository-data-tab" tabindex="0">
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="h4 mb-4">Dados do repositório</h2>
            <dl class="row mb-0">
                <dt class="col-md-4">Situação</dt><dd class="col-md-8"><?= esc($status) ?></dd>
                <dt class="col-md-4">Tipo de repositório</dt><dd class="col-md-8"><?= esc($types[$type] ?? ($type !== '' ? $type : 'Não informado')) ?></dd>
                <?php foreach ($fields as $field => $label): ?>
                    <dt class="col-md-4"><?= esc($label) ?></dt>
                    <dd class="col-md-8 text-break"><?= esc(($repository[$field] ?? '') !== '' && $repository[$field] !== null ? $repository[$field] : 'Não informado') ?></dd>
                <?php endforeach; ?>
                <?php foreach (['created_at' => 'Data de cadastro', 'updated_at' => 'Última movimentação', 'submitted_at' => 'Enviado em', 'seal_data_avaliation' => 'Avaliado em'] as $field => $label): ?>
                    <dt class="col-md-4"><?= esc($label) ?></dt>
                    <dd class="col-md-8"><?= !empty($repository[$field]) && $repository[$field] !== '0000-00-00 00:00:00' ? esc(date('d/m/Y H:i', strtotime($repository[$field]))) : 'Não informado' ?></dd>
                <?php endforeach; ?>
            </dl>
        </div>
    </div>
    </div>
    <?php $tabIndex = 0; foreach ($criteriaGroups as $level => $questions): $tabIndex++; ?>
    <div class="tab-pane fade" id="criteria-<?= $tabIndex ?>" role="tabpanel" aria-labelledby="criteria-<?= $tabIndex ?>-tab" tabindex="0">
        <h2 class="h4 mb-4">Critérios <?= esc((string) $level) ?></h2>
        <?php foreach ($questions as $question): ?>
                    <?php
                    $answer = $answers[(int) $question['id']] ?? [];
                    $value = (string) ($answer['resposta'] ?? '');
                    $decoded = json_decode($value, true);
                    if (is_array($decoded)) {
                        $value = implode(', ', array_map(static fn ($item) => is_scalar($item) ? (string) $item : json_encode($item, JSON_UNESCAPED_UNICODE), $decoded));
                    }
                    ?>
                    <?php
                    $answerClass = '';
                    if ($question['tipo_resposta'] === 'SN') {
                        $normalized = mb_strtolower(trim($value), 'UTF-8');
                        if (in_array($normalized, ['1', 'sim'], true)) {
                            $value = 'SIM';
                            $answerClass = 'text-success fw-bold';
                        } elseif (in_array($normalized, ['2', 'não', 'nao'], true)) {
                            $value = 'NÃO';
                            $answerClass = 'text-danger fw-bold';
                        }
                    }
                    ?>
        <article class="card mb-3"<?= $answerClass === 'text-danger fw-bold' ? ' style="background-color: #fff5f5;"' : ($answerClass === 'text-success fw-bold' ? ' style="background-color: #f0faf2;"' : '') ?>>
            <div class="card-body">
                <h3 class="h5"><?= esc($question['criterio']) ?> — <?= esc($question['questao']) ?></h3>
                <?php if (!empty($question['descricao'])): ?>
                    <p class="text-muted"><?= nl2br(esc($question['descricao'])) ?></p>
                <?php endif; ?>
                <?php if ($question['tipo_resposta'] !== 'INFO'): ?>
                    <div><strong>Resposta:</strong> <span class="text-break <?= $answerClass ?>"><?= $value !== '' ? nl2br(esc($value)) : 'Não respondido' ?></span></div>
                <?php endif; ?>
                <?php $comment = (string) ($answers[(int) $question['id']]['comentario'] ?? ''); ?>
                <?php if ($comment !== ''): ?>
                    <div class="mt-2 text-break"><strong>Comentário:</strong> <?= nl2br(esc($comment)) ?></div>
                <?php endif; ?>
                <?php $criterionEvidences = $evidencesByQuestion[(int) $question['id']] ?? []; ?>
                <?php if ($criterionEvidences): ?>
                    <div class="mt-3">
                        <h4 class="h6 fw-bold">Evidências</h4>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($criterionEvidences as $evidence): ?>
                                <?php
                                $url = (string) ($evidence['url'] ?? '');
                                $title = trim((string) ($evidence['titulo'] ?? '')) ?: $url;
                                $safeUrl = filter_var($url, FILTER_VALIDATE_URL)
                                    && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);
                                ?>
                                <li class="list-group-item px-0 text-break bg-transparent">
                                    <?php if ($safeUrl): ?>
                                        <a href="<?= esc($url, 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($title) ?> <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a>
                                    <?php else: ?>
                                        <?= esc($title) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($evidence['descricao'])): ?>
                                        <p class="mb-0 mt-1"><?= nl2br(esc($evidence['descricao'])) ?></p>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    </div>
</main>
<?= view('layout/footer') ?>
