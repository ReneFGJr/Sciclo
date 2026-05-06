INFO

<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title">Questão #<?= esc($q['id']) ?></h5>
        <p class="card-text mb-2"><?= esc($q['descricao']) ?></p>
        <?php if (!empty($q['alternativas'])): ?>
            <?php
            pre($q, false);
            ?>
        <?php endif; ?>
    </div>
</div>