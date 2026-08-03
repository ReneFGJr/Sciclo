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
                <?php
                $evidenceId = (int) ($evidence['id'] ?? 0);
                $evidenceTitle = (string) ($evidence['titulo'] ?? $evidence['url'] ?? 'Evidência');
                $evidenceUrl = (string) ($evidence['url'] ?? '');
                $evidenceDescription = (string) ($evidence['descricao'] ?? '');
                ?>
                <div class="list-group-item d-flex justify-content-between align-items-start gap-3">
                    <div class="me-3">
                        <a href="<?= esc($evidenceUrl) ?>" target="_blank" rel="noopener noreferrer" class="fw-semibold text-decoration-none">
                            <?= esc($evidenceTitle) ?>
                        </a>
                        <small class="text-muted d-block text-break"><?= esc($evidenceUrl) ?></small>
                        <?php if ($evidenceDescription !== ''): ?>
                            <small class="text-body-secondary d-block"><?= esc($evidenceDescription) ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary evidence-edit-btn"
                            data-bs-toggle="tooltip"
                            title="Editar evidência"
                            data-modal-target="#<?= esc($modalId) ?>"
                            data-evidence-id="<?= esc((string) $evidenceId) ?>"
                            data-evidence-url="<?= esc($evidenceUrl) ?>"
                            data-evidence-descricao="<?= esc($evidenceDescription) ?>"
                            data-evidence-title="<?= esc($evidenceTitle) ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger evidence-delete-btn"
                            data-bs-toggle="tooltip"
                            title="Excluir evidência"
                            data-delete-action="<?= base_url('application/evidence/delete/' . $evidenceId) ?>"
                            data-axis="<?= esc($currentAxis) ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                        <span class="badge bg-success-subtle text-success">Salva</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-muted small mb-2">Nenhuma evidência vinculada a esta questão.</div>
    <?php endif; ?>
</div>

<div class="modal fade" id="<?= esc($modalId) ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="evidence-form" data-action="<?= base_url('application/evidence/save') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Inserir evidência</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" class="evidence-current-axis" value="<?= esc($currentAxis) ?>">
                    <input type="hidden" class="evidence-question-id" value="<?= esc((string) $questionId) ?>">
                    <input type="hidden" class="evidence-edit-id" value="">

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
                    <button type="button" class="btn btn-primary evidence-submit-btn">Salvar evidência</button>
                </div>
            </div>
        </div>
    </div>
</div>
