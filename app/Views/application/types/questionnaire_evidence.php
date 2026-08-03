<?php
$q = isset($q) && is_array($q) ? $q : [];
$nivel2 = trim((string) ($q['nivel2'] ?? ''));
$nivel2Vazio = $nivel2 === '';

if ($nivel2Vazio) {
    return;
}

$questionId = (int) ($q['id'] ?? 0);
$modalId = 'evidenceModal' . $questionId;
$questionEvidences = isset($q['evidencias']) && is_array($q['evidencias']) ? $q['evidencias'] : [];
$existingEvidences = isset($q['existing_evidences']) && is_array($q['existing_evidences']) ? $q['existing_evidences'] : [];
$currentAxis = is_scalar($q['current_axis'] ?? null) ? (string) $q['current_axis'] : '';
?>

<div class="mt-3 pt-3 border-top">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
        <div>
            <div class="fw-bold">Evidências</div>
            <small class="text-muted">Anexe um link ou reutilize uma evidência já cadastrada.</small>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#<?= esc($modalId) ?>">
            Inserir evidência
        </button>
    </div>

    <?php if (!empty($questionEvidences)): ?>
        <div class="list-group mb-2">
            <?php foreach ($questionEvidences as $evidence): ?>
                <a href="<?= esc($evidence['url']) ?>" target="_blank" rel="noopener noreferrer" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                    <div class="me-3">
                        <div class="fw-semibold"><?= esc($evidence['titulo'] ?? $evidence['url']) ?></div>
                        <small class="text-muted d-block text-break"><?= esc($evidence['url']) ?></small>
                        <?php if (!empty($evidence['descricao'])): ?>
                            <small class="text-body-secondary d-block"><?= esc($evidence['descricao']) ?></small>
                        <?php endif; ?>
                    </div>
                    <span class="badge bg-success-subtle text-success">Salva</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-muted small mb-2">Nenhuma evidência vinculada a esta questão.</div>
    <?php endif; ?>
</div>

<div class="modal fade" id="<?= esc($modalId) ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="post" action="<?= base_url('application/evidence/save') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Inserir evidência</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="current_axis" value="<?= esc($currentAxis) ?>">
                    <input type="hidden" name="questao_id" value="<?= esc((string) $questionId) ?>">

                    <?php if (!empty($existingEvidences)): ?>
                        <div class="mb-3">
                            <label class="form-label" for="evidence_existing_<?= esc((string) $questionId) ?>">Reutilizar evidência já cadastrada</label>
                            <select class="form-select evidence-existing-select" id="evidence_existing_<?= esc((string) $questionId) ?>" name="evidence_id">
                                <option value="">Nova evidência</option>
                                <?php foreach ($existingEvidences as $evidence): ?>
                                    <option
                                        value="<?= esc((string) ($evidence['id'] ?? '')) ?>"
                                        data-url="<?= esc((string) ($evidence['url'] ?? '')) ?>"
                                        data-descricao="<?= esc((string) ($evidence['descricao'] ?? '')) ?>">
                                        <?= esc((string) ($evidence['titulo'] ?? $evidence['url'] ?? 'Evidência')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label" for="evidence_url_<?= esc((string) $questionId) ?>">URL da evidência</label>
                        <input type="url" class="form-control evidence-url" id="evidence_url_<?= esc((string) $questionId) ?>" name="url" placeholder="https://..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="evidence_desc_<?= esc((string) $questionId) ?>">Descrição</label>
                        <textarea class="form-control evidence-description" id="evidence_desc_<?= esc((string) $questionId) ?>" name="descricao" rows="4" placeholder="Descreva o que esta evidência comprova"></textarea>
                    </div>

                    <div class="alert alert-info mb-0">
                        O título será obtido automaticamente a partir da URL informada.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar evidência</button>
                </div>
            </form>
        </div>
    </div>
</div>