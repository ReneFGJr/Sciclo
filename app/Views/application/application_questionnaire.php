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

  <?php if (session()->getFlashdata('questionnaire_error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('questionnaire_error')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('questionnaire_success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('questionnaire_success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('evidence_error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('evidence_error')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('evidence_success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('evidence_success')) ?></div>
  <?php endif; ?>
  <?php
    $evidenceFlashQuestion = (int) (session()->getFlashdata('evidence_modal_question') ?? 0);
    $evidenceFlashError = session()->getFlashdata('evidence_error');
    $evidenceFlashSuccess = session()->getFlashdata('evidence_success');
  ?>

  <div class="row g-4">
    <aside class="col-lg-3">
      <div class="card shadow-sm">
        <div class="card-header fw-bold">Eixos</div>
        <div class="list-group list-group-flush">
          <?php if (!empty($axes)): ?>
            <?php foreach ($axes as $axis): ?>
              <?php $axisKey = (string) ($axis['eixo'] ?? ''); ?>
              <a
                href="<?= base_url('application/form/' . $axisKey) ?>"
                class="list-group-item list-group-item-action <?= ((string) ($current_axis ?? '') === $axisKey) ? 'active' : '' ?>">
                <div class="fw-semibold">Eixo <?= esc($axisKey) ?></div>
                <small><?= nl2br(glossario_conteudo($axis['titulo'] ?? '')) ?></small>
              </a>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="list-group-item">Nenhum eixo encontrado.</div>
          <?php endif; ?>
        </div>
      </div>
    </aside>

    <section class="col-lg-9">
      <?php foreach ($questions ?? [] as $question): ?>
        <?php
          $evidenceQuestionId = (int) ($question['id'] ?? 0);
          $hasEvidenceForm = trim((string) ($question['nivel2'] ?? '')) !== '';
        ?>
        <?php if ($hasEvidenceForm && $evidenceQuestionId > 0): ?>
          <form id="evidence-form-<?= esc((string) $evidenceQuestionId) ?>"></form>
        <?php endif; ?>
      <?php endforeach; ?>

      <form method="post" action="<?= base_url('application/submit_questionnaire') ?>" data-answer-save-url="<?= base_url('application/answer/save') ?>">
        <input type="hidden" name="current_axis" value="<?= esc((string) ($current_axis ?? '')) ?>">

        <?php if (!empty($questions)): ?>
          <?php $level2SectionOpen = false; ?>
          <?php foreach ($questions as $q): ?>
            <?php
            $qid = (int) ($q['id'] ?? 0);
            $q['saved_answer'] = $saved_answers[$qid] ?? null;
            $q['saved_comment'] = $saved_comments[$qid] ?? null;
            $q['evidencias'] = $evidences_by_question[$qid] ?? [];
            $q['existing_evidences'] = $existing_evidences ?? [];
            $q['current_axis'] = $current_axis ?? '';
            $q['evidence_flash_question'] = $evidenceFlashQuestion;
            $q['evidence_flash_error'] = $evidenceFlashError;
            $q['evidence_flash_success'] = $evidenceFlashSuccess;
            $isLevel2Heading = ($q['tipo_resposta'] ?? '') === 'INFO'
              && trim((string) ($q['nivel2'] ?? '')) !== ''
              && trim((string) ($q['nivel3'] ?? '')) === '';

            if ($isLevel2Heading) {
              if ($level2SectionOpen) {
                echo '</div></section>';
              }

              $level2 = trim((string) ($q['nivel2'] ?? ''));
              echo '<section class="border rounded-4 p-3 p-md-4 mb-5 bg-light">';
              echo '<h2 class="h3 mb-4">' . esc($level2) . ' - ' . glossario_conteudo($q['questao'] ?? '') . '</h2>';
              echo '<div class="level-3-question-group">';
              $level2SectionOpen = true;
              continue;
            }
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

          <?php if ($level2SectionOpen): ?>
            </div></section>
          <?php endif; ?>

          <div class="d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">Salvar e continuar</button>
          </div>
        <?php else: ?>
          <div class="alert alert-warning">Nenhuma questão disponível para este eixo.</div>
        <?php endif; ?>
      </form>
    </section>
  </div>
</div>

<?php
echo view('layout/footer');
