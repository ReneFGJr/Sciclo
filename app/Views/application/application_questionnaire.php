<?php
echo view('layout/header');
echo view('layout/navbar');
/**
 * application_questionnaire.php
 * View para exibir um questionário de certificação
 *
 * Espera um array $questions com as questões e campos opcionais para respostas.
 * Cada questão deve ter: id, enunciado, alternativas (array), condicional_1, condicional_2
 */
?>
<div class="container py-4">
  <h2 class="mb-4">Questionário de Certificação</h2>
  <form method="post" action="<?= base_url('application/submit_questionnaire') ?>">
    <?php if (!empty($questions)): ?>
      <?php foreach ($questions as $q): ?>
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
            <?php if (!empty($q['condicional_1'])): ?>
              <div class="mt-2 alert alert-info p-2 small">Condicional 1: <?= esc($q['condicional_1']) ?></div>
            <?php endif; ?>
            <?php if (!empty($q['condicional_2'])): ?>
              <div class="mt-2 alert alert-info p-2 small">Condicional 2: <?= esc($q['condicional_2']) ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <button type="submit" class="btn btn-primary">Enviar respostas</button>
    <?php else: ?>
      <div class="alert alert-warning">Nenhuma questão disponível.</div>
    <?php endif; ?>
  </form>
</div>

<?php
echo view('layout/footer');
