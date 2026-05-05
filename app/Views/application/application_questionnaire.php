
<div class="container py-4">
  <h2 class="mb-4">Questionário de Certificação</h2>
  <form method="post" action="<?= base_url('application/submit_questionnaire') ?>">
    <?php if (!empty($question)): ?>
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Questão #<?= esc($question['id']) ?></h5>
          <dl class="row mb-0">
            <dt class="col-sm-3">Critério</dt>
            <dd class="col-sm-9"><?= esc($question['criterio']) ?></dd>

            <dt class="col-sm-3">Nível 1</dt>
            <dd class="col-sm-9"><?= esc($question['nivel1']) ?></dd>

            <dt class="col-sm-3">Nível 2</dt>
            <dd class="col-sm-9"><?= esc($question['nivel2']) ?></dd>

            <dt class="col-sm-3">Nível 3</dt>
            <dd class="col-sm-9"><?= esc($question['nivel3']) ?></dd>

            <dt class="col-sm-3">Questão</dt>
            <dd class="col-sm-9"><?= esc($question['questao']) ?></dd>

            <dt class="col-sm-3">Tipo de Resposta</dt>
            <dd class="col-sm-9"><?= esc($question['tipo_resposta']) ?></dd>

            <dt class="col-sm-3">Descrição</dt>
            <dd class="col-sm-9"><?= esc($question['descricao']) ?></dd>

            <dt class="col-sm-3">Ícone</dt>
            <dd class="col-sm-9"><?= esc($question['icone']) ?></dd>

            <dt class="col-sm-3">Imagem</dt>
            <dd class="col-sm-9"><?= esc($question['imagem']) ?></dd>

            <dt class="col-sm-3">Condicional 1</dt>
            <dd class="col-sm-9"><?= esc($question['condicional_1']) ?></dd>

            <dt class="col-sm-3">Condicional 2</dt>
            <dd class="col-sm-9"><?= esc($question['condicional_2']) ?></dd>
          </dl>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Confirmar</button>
    <?php else: ?>
      <div class="alert alert-warning">Nenhuma questão disponível.</div>
    <?php endif; ?>
  </form>
</div>
