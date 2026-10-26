<?php
echo view('layout/header');
echo view('layout/navbar');
?>
<div class="container py-5">
    <?php if (!empty($revision)): ?><div class="alert alert-warning"><h2 class="h5">Revisões necessárias</h2><p class="mb-2"><?= nl2br(esc($revision['details'])) ?></p><p class="mb-0">Complete as informações e confirme o reenvio para avaliação ao concluir.</p></div><?php endif ?>
    <?php $selectedType = trim((string) ($repo['repository_type'] ?? '')); ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <h1 class="fw-bold mb-4">Selecione o Questionário de Certificação</h1>
            <p class="mb-4">Escolha abaixo o tipo de questionário que deseja responder para iniciar o processo de avaliação do seu repositório.</p>
            <?php if (!empty($repo)): ?>
                <section class="card bg-light border-0 mb-4" aria-labelledby="repository-summary-title">
                    <div class="card-body">
                        <h2 class="h5 mb-3" id="repository-summary-title">Resumo do reposit&#243;rio</h2>
                        <dl class="row mb-0">
                            <?php
                            $summaryFields = [
                                'repository_name' => 'Nome',
                                'base_url' => 'URL do repositório',
                                'base_url_oai' => 'URL OAI-PMH',
                                'repository_software_name' => 'Software',
                                'repository_software_version' => 'Versão do software',
                                'protocol_version' => 'Versão OAI-PMH',
                                'admin_email' => 'E-mail de contato',
                            ];
                            ?>
                            <?php foreach ($summaryFields as $field => $label): ?>
                                <?php $value = trim((string) ($repo[$field] ?? '')); ?>
                                <?php if ($value === '') continue; ?>
                                <dt class="col-sm-4"><?= esc($label) ?></dt>
                                <dd class="col-sm-8 text-break"><?= esc($value) ?></dd>
                            <?php endforeach; ?>
                        </dl>
                    </div>
                </section>
            <?php endif; ?>
            <?php if (isset($progress)): ?>
                <section class="card border-0 bg-light mb-4" aria-labelledby="completion-summary-title">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                            <h2 class="h5 mb-0" id="completion-summary-title">Resumo de preenchimento</h2>
                            <strong class="text-success"><?= esc(number_format($progress['completion'], 1, ',', '.')) ?>%</strong>
                        </div>
                        <div class="progress mb-2" role="progressbar" aria-label="Preenchimento do question&#225;rio" aria-valuenow="<?= esc((string) $progress['completion']) ?>" aria-valuemin="0" aria-valuemax="100" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: <?= esc((string) $progress['completion']) ?>%"></div>
                        </div>
                        <p class="small text-muted mb-3"><?= (int) $progress['answered'] ?> de <?= (int) $progress['total'] ?> quest&#245;es preenchidas. Faltam <?= (int) $progress['pending'] ?> quest&#245;es (<?= esc(number_format(100 - $progress['completion'], 1, ',', '.')) ?>%).</p>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th scope="col">Eixo</th><th scope="col" class="text-end">Preenchidas</th><th scope="col" class="text-end">Pendentes</th><th scope="col" class="text-end">Percentual</th></tr></thead>
                                <tbody>
                                    <?php foreach ($progress['groups'] as $level => $group): ?>
                                        <tr>
                                            <th scope="row"><?= esc((string) $level) ?><?= !empty($progress['axis_titles'][$level]) ? ' - ' . esc($progress['axis_titles'][$level]) : '' ?></th>
                                            <td class="text-end"><?= (int) $group['answered'] ?> / <?= (int) $group['total'] ?></td>
                                            <td class="text-end"><?= (int) $group['pending'] ?></td>
                                            <td class="text-end <?= $group['pending'] === 0 ? 'text-success' : '' ?>"><?= esc(number_format($group['completion'], 1, ',', '.')) ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($progress['groups'])): ?>
                                        <tr><td colspan="4" class="text-muted">Nenhuma quest&#227;o cadastrada.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <p class="small text-muted mt-2 mb-0">Quest&#245;es puladas automaticamente contam como preenchidas.</p>
                    </div>
                </section>
            <?php endif; ?>
            <form id="revalidate-repository" method="post" action="<?= site_url('application') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="repo_link" value="<?= esc($repo['base_url'] ?? '', 'attr') ?>">
            </form>
            <form method="post" action="<?= site_url('application/form/select/' . $repo['id']); ?>">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label for="questionario" class="form-label">Tipo de Questionário</label>
                    <select class="form-select form-select-lg" id="questionario" name="questionario" required>
                        <option value="" <?= $selectedType === '' ? 'selected' : ''; ?> disabled>Selecione...</option>
                        <option value="publicacao" <?= $selectedType === 'publicacao' ? 'selected' : ''; ?>>Certificação de Repositório de Publicação</option>
                        <option value="dados" <?= $selectedType === 'dados' ? 'selected' : ''; ?>>Certificação de Repositório de Dados</option>
                        <option value="ambos" <?= $selectedType === 'ambos' ? 'selected' : ''; ?>>Certificação de Repositório de Publicação e Dados</option>
                        <option value="arquivistico" <?= $selectedType === 'arquivistico' ? 'selected' : ''; ?>>Certificação de Repositório Arquivístico</option>
                    </select>
                </div>
                <div class="d-flex flex-wrap justify-content-between gap-3">
                    <button type="submit" class="btn btn-primary btn-lg px-5"><?= !empty($repo['id']) ? 'Continuar preenchimento' : 'Iniciar' ?></button>
                    <button type="submit" form="revalidate-repository" class="btn btn-outline-primary btn-lg" <?= empty($repo['base_url']) ? 'disabled' : '' ?>>Revalidar URL</button>
                </div>
            </form>
        </div>
        <aside class="col-lg-4" aria-labelledby="repository-metadata-title">
            <section class="card border-0 bg-light">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <h2 class="h5 mb-0" id="repository-metadata-title">Modelos de metadados</h2>
                        <span class="badge bg-primary"><?= count($metadataFormats ?? []) ?></span>
                    </div>
                    <?php if (!empty($metadataFormats)): ?>
                        <p class="small text-muted">Formatos anunciados pelo OAI-PMH deste reposit&#243;rio.</p>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($metadataFormats as $format): ?>
                                <li class="border-top py-2 text-break"><?= esc($format['metadata_prefix']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="small text-muted mb-0">Nenhum modelo de metadados cadastrado. Clique em Revalidar URL para consultar os formatos do reposit&#243;rio.</p>
                    <?php endif; ?>
                </div>
            </section>
        </aside>
    </div>
</div>
<?php
echo view('layout/footer');
?>
