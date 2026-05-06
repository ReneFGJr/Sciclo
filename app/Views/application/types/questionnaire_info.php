<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title">Questão #<?= esc($q['id']) ?></h5>
        <p class="card-text mb-2"><?= esc($q['descricao']) ?></p>
        <?php if (!empty($q['alternativas'])): ?>
            <?php foreach ($q['alternativas'] as $alt): ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="questao_<?= esc($q['id']) ?>" id="alt_<?= esc($q['id']) ?>_<?= esc($alt['id']) ?>" value="<?= esc($alt['id']) ?>">
                    <label class="form-check-label" for="alt_<?= esc($q['id']) ?>_<?= esc($alt['questao']) ?>">
                        <?= esc($alt['texto']) ?>
                    </label>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php pre($q, false); ?>