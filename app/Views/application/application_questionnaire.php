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
        <?php
        echo '<h1>'.$q['tipo_resposta'].'</h1>';
        switch ($q['tipo_resposta']) {
          case 'INFO':
            echo view('application/types/questionnaire_info', ['q' => $q]);
            break;
          case 'SN':
            echo view('application/types/questionnaire_sn', ['q' => $q]);
            break;
          default:
            echo view('application/types/questionnaire_default', ['q' => $q]);
            break;
        }
        ?>
      <?php endforeach; ?>
      <button type="submit" class="btn btn-primary">Enviar respostas</button>
    <?php else: ?>
      <div class="alert alert-warning">Nenhuma questão disponível.</div>
    <?php endif; ?>
  </form>
</div>

<?php
echo view('layout/footer');
