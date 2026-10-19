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
<style>
  .questionnaire-branch-question { scroll-margin-top: 6rem; }
  .questionnaire-answered {
    border: 2px solid #198754;
    border-radius: 24px;
    background: #f0fdf4;
    margin-bottom: 1.5rem;
  }
  .questionnaire-answered .card {
    background: #f0fdf4;
    margin-bottom: 0 !important;
    box-shadow: none;
  }

  .questionnaire-pending {
    border: 2px solid #dc3545;
    border-radius: 24px;
    background: #fff1f2;
    margin-bottom: 1.5rem;
  }
  .questionnaire-pending .card {
    background: #fff1f2;
    margin-bottom: 0 !important;
    box-shadow: none;
  }

  .questionnaire-skipped-description {
    margin-bottom: 1rem;
    padding: 0.65rem 0.85rem;
    border-left: 3px solid #ea580c;
    border-radius: 4px;
    background: #fff7ed;
    color: #9a3412;
    font-size: 0.9rem;
  }
  .questionnaire-skipped-description .small {
    color: #b45309;
  }
</style>
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
              <?php
                $axisKey = (string) ($axis['eixo'] ?? '');
                $isCurrentAxis = (string) ($current_axis ?? '') === $axisKey;
                $submenuId = 'axis-submenu-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $axisKey);
              ?>
              <div class="list-group-item p-0 <?= $isCurrentAxis ? 'border-primary' : '' ?>">
                <div class="d-flex align-items-stretch">
                  <a href="<?= base_url('application/form/' . $axisKey) ?>" class="list-group-item-action p-3 text-decoration-none <?= $isCurrentAxis ? 'bg-primary text-white' : 'text-body' ?>">
                    <div class="fw-semibold">Eixo <?= esc($axisKey) ?></div>
                    <small><?= nl2br(glossario_conteudo($axis['titulo'] ?? '')) ?></small>
                  </a>
                  <?php if (!empty($axis['sublevels'])): ?>
                    <button class="btn <?= $isCurrentAxis ? 'btn-primary' : 'btn-light' ?> rounded-0 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#<?= esc($submenuId) ?>" aria-expanded="<?= $isCurrentAxis ? 'true' : 'false' ?>" aria-controls="<?= esc($submenuId) ?>" aria-label="Mostrar critérios do eixo <?= esc($axisKey) ?>">&#9662;</button>
                  <?php endif; ?>
                </div>

                <?php if (!empty($axis['sublevels'])): ?>
                  <div id="<?= esc($submenuId) ?>" class="collapse <?= $isCurrentAxis ? 'show' : '' ?>">
                    <div class="list-group list-group-flush border-top">
                      <?php foreach ($axis['sublevels'] as $sublevel): ?>
                        <a class="list-group-item list-group-item-action ps-4 py-2 small <?= $isCurrentAxis && (string) ($current_level2 ?? '') === (string) $sublevel['nivel2'] ? 'active' : '' ?>" href="<?= base_url('application/form/' . $axisKey . '/' . $sublevel['nivel2']) ?>">
                          <strong><?= esc($sublevel['criterio']) ?></strong> - <?= esc($sublevel['titulo']) ?>
                        </a>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="list-group-item">Nenhum eixo encontrado.</div>
          <?php endif; ?>
        </div>
      </div>
    </aside>

    <section class="col-lg-9">
      <?php $progress = $questionnaire_progress ?? ['completion' => 0, 'answered' => 0, 'total' => 0]; ?>
      <div class="mb-4" id="questionnaire-progress" aria-live="polite">
        <div class="d-flex flex-wrap justify-content-between gap-1 small mb-2">
          <strong>Preenchimento do question&#225;rio: <span data-progress-completion><?= esc(number_format($progress['completion'], 1, ',', '.')) ?></span>%</strong>
          <span class="text-muted">Faltam <span data-progress-remaining><?= esc(number_format(100 - $progress['completion'], 1, ',', '.')) ?></span>% para 100%</span>
        </div>
        <div class="progress" role="progressbar" aria-label="Preenchimento do question&#225;rio" aria-valuenow="<?= esc((string) $progress['completion']) ?>" aria-valuemin="0" aria-valuemax="100" style="height: 10px;">
          <div class="progress-bar bg-success" style="width: <?= esc((string) $progress['completion']) ?>%"></div>
        </div>
        <div class="small text-muted mt-1"><span data-progress-answered><?= esc((string) $progress['answered']) ?></span> de <span data-progress-total><?= esc((string) $progress['total']) ?></span> quest&#245;es preenchidas</div>
      </div>
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
        <input type="hidden" name="current_level2" value="<?= esc((string) ($current_level2 ?? '')) ?>">

        <?php if (!empty($questions)): ?>
          <?php $level2SectionOpen = false; $currentLevel2 = null; ?>
          <?php foreach ($questions as $q): ?>
            <?php
            $qid = (int) ($q['id'] ?? 0);
            $q['software_validated'] = in_array($qid, $software_validated_ids ?? [], true);
            $q['saved_answer'] = $saved_answers[$qid] ?? null;
            $q['saved_comment'] = $saved_comments[$qid] ?? null;
            $q['evidencias'] = $evidences_by_question[$qid] ?? [];
            $q['existing_evidences'] = $existing_evidences ?? [];
            $q['current_axis'] = $current_axis ?? '';
            $q['evidence_flash_question'] = $evidenceFlashQuestion;
            $q['evidence_flash_error'] = $evidenceFlashError;
            $q['evidence_flash_success'] = $evidenceFlashSuccess;
            $level2 = trim((string) ($q['nivel2'] ?? ''));
            $level3 = trim((string) ($q['nivel3'] ?? ''));
            $startsLevel2Group = $level2 !== '' && $level3 !== '' && $level2 !== $currentLevel2;

            if ($startsLevel2Group) {
              if ($level2SectionOpen) {
                echo '</div></section>';
              }

              $sectionId = 'nivel2-' . ($q['nivel1'] ?? '') . '-' . $level2;
              echo '<section id="' . esc($sectionId) . '" class="border rounded-4 p-3 p-md-4 mb-5 bg-light scroll-margin-top">';
              echo '<h2 class="h3 mb-4">' . esc(($q['nivel1'] ?? '') . '.' . $level2) . ' - ' . glossario_conteudo($q['questao'] ?? '') . '</h2>';
              echo '<div class="level-3-question-group">';
              $level2SectionOpen = true;
              $currentLevel2 = $level2;
            }
            $answerValue = trim((string) ($q['saved_answer'] ?? ''));
            $decodedAnswer = json_decode($answerValue, true);
            $isAnswered = $q['tipo_resposta'] !== 'INFO' && $answerValue !== ''
                && !(is_array($decodedAnswer) && $decodedAnswer === []);
            $isPending = !empty($highlight_pending) && $q['tipo_resposta'] !== 'INFO' && !$isAnswered;
            $answerClass = $isAnswered ? ' questionnaire-answered' : ($isPending ? ' questionnaire-pending' : '');
            echo '<div id="question-' . $qid . '" class="questionnaire-branch-question' . $answerClass . '" data-question-id="' . $qid . '" data-answered="' . ($isAnswered ? '1' : '0') . '">';
            $skippedNumber = trim((string) ($q['nivel3'] ?? ''));
            echo '<div class="questionnaire-skipped-description" hidden>';
            echo '<p class="mb-1">' . ($skippedNumber !== '' ? '<strong>' . esc($skippedNumber) . ' - </strong>' : '') . nl2br(glossario_conteudo($q['descricao'] ?? '')) . '</p>';
            echo '<p class="small mb-0">N&#227;o pertinente, por negativa da quest&#227;o anterior</p>';
            echo '</div>';
            echo '<fieldset class="questionnaire-branch-content">';
            switch ($q['tipo_resposta']) {
              case 'INFO':
                echo view('application/types/questionnaire_info', ['q' => $q]);
                break;
              case 'SOFTWARE':
              case 'SN':
                echo view('application/types/questionnaire_sn', ['q' => $q]);
                break;
              default:
                echo view('application/types/questionnaire_default', ['q' => $q]);
                break;
            }
            echo '</fieldset></div>';
            ?>
          <?php endforeach; ?>

          <?php if ($level2SectionOpen): ?>
            </div></section>
          <?php endif; ?>

          <div class="d-flex flex-wrap justify-content-between gap-2">
            <button type="submit" name="save_for_later" value="1" class="btn btn-outline-primary" formnovalidate>Salvar e Continuar Depois</button>
            <button type="submit" class="btn btn-primary"><?= !empty($is_last_question_group) ? 'Salvar e revisar submiss&#227;o' : 'Salvar e continuar' ?></button>
          </div>
        <?php else: ?>
          <div class="alert alert-warning">Nenhuma questão disponível para este eixo.</div>
        <?php endif; ?>
      </form>
    </section>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[data-answer-save-url]');
    if (!form) return;
    const progress = document.getElementById('questionnaire-progress');
    form.addEventListener('questionnaire:progress-saved', event => {
        const data = event.detail;
        progress.querySelector('[data-progress-completion]').textContent = Number(data.completion).toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
        progress.querySelector('[data-progress-remaining]').textContent = (100 - Number(data.completion)).toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
        progress.querySelector('[data-progress-answered]').textContent = data.answered;
        progress.querySelector('[data-progress-total]').textContent = data.total;
        progress.querySelector('[role="progressbar"]').setAttribute('aria-valuenow', data.completion);
        progress.querySelector('.progress-bar').style.width = data.completion + '%';
    });
    const questions = Array.from(form.querySelectorAll('.questionnaire-branch-question'));
    const initialSkipped = <?= json_encode($skipped_question_ids ?? []) ?>;
    function applySkipped(ids) {
        const skipped = new Set(ids.map(String));
        questions.forEach(wrapper => {
            const isSkipped = skipped.has(wrapper.dataset.questionId);
            const content = wrapper.querySelector('.questionnaire-branch-content');
            const summary = wrapper.querySelector('.questionnaire-skipped-description');
            content.disabled = isSkipped;
            content.hidden = isSkipped;
            summary.hidden = !isSkipped;
            wrapper.dataset.skipped = isSkipped ? '1' : '0';
            if (isSkipped) {
                wrapper.classList.remove('questionnaire-pending');
                wrapper.dataset.answered = '1';
            }
            wrapper.classList.toggle('questionnaire-answered', !isSkipped && wrapper.dataset.answered === '1');
            let hiddenAnswer = wrapper.querySelector('.questionnaire-skipped-answer');
            if (isSkipped) {
                const noInput = content.querySelector('.questionnaire-sn-input[value="2"]');
                if (noInput) {
                    noInput.checked = true;
                    noInput.closest('.questionnaire-sn-card').dataset.savedAnswer = '2';
                }
                if (!hiddenAnswer) {
                    hiddenAnswer = document.createElement('input');
                    hiddenAnswer.type = 'hidden';
                    hiddenAnswer.className = 'questionnaire-skipped-answer';
                    hiddenAnswer.name = 'questao_' + wrapper.dataset.questionId;
                    hiddenAnswer.value = '2';
                    wrapper.appendChild(hiddenAnswer);
                }
            } else if (hiddenAnswer) {
                hiddenAnswer.remove();
            }
        });
    }
    applySkipped(initialSkipped);
    form.addEventListener('questionnaire:branches-saved', event => applySkipped(event.detail));
});
</script>

<?php
echo view('layout/footer');
