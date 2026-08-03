<?php
$q = isset($q) && is_array($q) ? $q : [];
?>

<div class="card mb-3">
    <div class="card-body">
        <?php $selectedAnswer = is_scalar($q['saved_answer'] ?? null) ? trim((string) $q['saved_answer']) : ''; ?>
        <h5 class="card-title"><?= nl2br(glossario_conteudo($q['questao'] ?? ('Questão #' . ($q['id'] ?? '')))) ?></h5>
        <p class="card-text mb-2"><?= nl2br(glossario_conteudo($q['descricao'] ?? '')) ?></p>

        <?php if (!empty($q['alternativas'])): ?>
            <?php foreach ($q['alternativas'] as $alt): ?>
                <?php $optionId = is_scalar($alt['id'] ?? null) ? (string) $alt['id'] : ''; ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="questao_<?= esc($q['id']) ?>" id="alt_<?= esc($q['id']) ?>_<?= esc($alt['id']) ?>" value="<?= esc($alt['id']) ?>" <?= $selectedAnswer === $optionId ? 'checked' : '' ?>>
                    <label class="form-check-label" for="alt_<?= esc($q['id']) ?>_<?= esc($alt['id']) ?>">
                        <?= esc($alt['texto'] ?? '') ?>
                    </label>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?= view('application/types/questionnaire_evidence', ['q' => $q]) ?>
    </div>
</div>