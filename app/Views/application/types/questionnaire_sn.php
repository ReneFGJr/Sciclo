<?php
$q = isset($q) && is_array($q) ? $q : [];
?>

<?php if (!defined('QUESTIONNAIRE_SN_ASSETS_LOADED')): ?>
    <?php define('QUESTIONNAIRE_SN_ASSETS_LOADED', true); ?>
    <style>
        .questionnaire-sn-card {
            border: 1px solid rgba(18, 38, 63, 0.08);
            border-radius: 24px;
            background: linear-gradient(135deg, #ffffff 0%, #f5f9ff 100%);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
            opacity: 0;
            transform: translateY(18px);
            animation: questionnaire-sn-fade-in 0.55s ease-out forwards;
        }

        .questionnaire-sn-card .card-body {
            padding: 1.75rem;
        }

        .questionnaire-sn-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .questionnaire-sn-title {
            margin: 1rem 0 0.35rem;
            color: #0f172a;
            font-size: 1.3rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .questionnaire-sn-subtitle {
            margin: 0 0 1.25rem;
            color: #475569;
            font-size: 0.98rem;
        }

        .questionnaire-sn-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 1rem;
        }

        .questionnaire-sn-comment {
            margin-top: 1.25rem;
        }

        .questionnaire-sn-comment textarea {
            min-height: 110px;
            resize: vertical;
        }

        .questionnaire-sn-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .questionnaire-sn-label {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
            min-height: 70px;
            padding: 1.15rem;
            border: 1px solid #dbe4f0;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.92);
            cursor: pointer;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease, background 0.22s ease;
        }

        .questionnaire-sn-label:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
            border-color: #93c5fd;
        }

        .questionnaire-sn-label.is-yes .questionnaire-sn-pill {
            background: #dcfce7;
            color: #166534;
        }

        .questionnaire-sn-label.is-no .questionnaire-sn-pill {
            background: #fee2e2;
            color: #991b1b;
        }

        .questionnaire-sn-label.is-neutral .questionnaire-sn-pill {
            background: #e2e8f0;
            color: #334155;
        }

        .questionnaire-sn-pill {
            align-self: flex-start;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .questionnaire-sn-text {
            color: #0f172a;
            font-size: 2.05rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .questionnaire-sn-helper {
            color: #64748b;
            font-size: 0.9rem;
        }

        .questionnaire-sn-check {
            margin-top: auto;
            color: #2563eb;
            font-size: 0.82rem;
            font-weight: 700;
            opacity: 0;
            transition: opacity 0.22s ease;
        }

        .questionnaire-sn-input:checked+.questionnaire-sn-label {
            border-color: #2563eb;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            box-shadow: 0 18px 36px rgba(37, 99, 235, 0.16);
            transform: translateY(-2px);
        }

        .questionnaire-sn-input:checked+.questionnaire-sn-label .questionnaire-sn-check {
            opacity: 1;
        }

        @keyframes questionnaire-sn-fade-in {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 576px) {
            .questionnaire-sn-card .card-body {
                padding: 1.25rem;
            }

            .questionnaire-sn-options {
                grid-template-columns: 1fr;
            }
        }

        .text-color-1 {
            color: #16a34a;
        }
        .text-color-2 {
            color: #dc2626;
        }
    </style>
<?php endif; ?>

<?php
$questionId = (int) ($q['id'] ?? 0);
$questionIdText = (string) $questionId;
$questionDescription = is_scalar($q['descricao'] ?? null) ? (string) $q['descricao'] : '';
$selectedAnswer = is_scalar($q['saved_answer'] ?? null) ? trim((string) $q['saved_answer']) : '';
$savedComment = is_scalar($q['saved_comment'] ?? null) ? (string) $q['saved_comment'] : '';
$animationDelay = (($questionId % 5) * 90) . 'ms';
$nivel3 = trim((string) ($q['nivel3'] ?? ''));
$q['alternativas'] = [
    ['id' => 1, 'texto' => 'Sim'],
    ['id' => 2, 'texto' => 'Não']
];
?>

<div class="card mb-4 questionnaire-sn-card" style="animation-delay: <?= esc($animationDelay) ?>;">
    <div class="card-body">
        <p class="questionnaire-sn-subtitle"><?= $nivel3 !== '' ? '<strong>' . esc($nivel3) . ' - </strong>' : '' ?><?= nl2br(glossario_conteudo($questionDescription)) ?></p>

        <?php if (!empty($q['alternativas'])): ?>
            <div class="questionnaire-sn-options" role="radiogroup" aria-label="Questão <?= esc($questionIdText) ?>">
                <?php foreach ($q['alternativas'] as $alt): ?>
                    <?php
                    $optionId = is_scalar($alt['id'] ?? null) ? (string) $alt['id'] : '';
                    $optionText = trim((string) ($alt['texto'] ?? ''));
                    $normalizedText = function_exists('mb_strtolower') ? mb_strtolower($optionText, 'UTF-8') : strtolower($optionText);
                    $labelClass = 'is-neutral';

                    if ($normalizedText === 'sim') {
                        $labelClass = 'is-yes';
                    } elseif ($normalizedText === 'não' || $normalizedText === 'nao') {
                        $labelClass = 'is-no';
                    }
                    ?>
                    <div class="questionnaire-sn-option">
                        <input
                            class="questionnaire-sn-input"
                            type="radio"
                            name="questao_<?= esc($questionIdText) ?>"
                            id="alt_<?= esc($questionIdText) ?>_<?= esc($optionId) ?>"
                            value="<?= esc($optionId) ?>"
                            <?= $selectedAnswer === $optionId ? 'checked' : '' ?>
                            required>
                        <label class="questionnaire-sn-label <?= esc($labelClass) ?>" for="alt_<?= esc($questionIdText) ?>_<?= esc($optionId) ?>">
                            <span class="questionnaire-sn-text text-color-<?= esc($optionId) ?>"><?= esc($optionText) ?></span>
                            <span class="questionnaire-sn-check">Resposta selecionada</span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class='questionnaire-sn-comment'>
            <label class='form-label fw-semibold' for='comentario_<?= esc($questionIdText) ?>'>Coment&#225;rios</label>
            <textarea class='form-control questionnaire-sn-comment-input' name='comentario_<?= esc($questionIdText) ?>' id='comentario_<?= esc($questionIdText) ?>' maxlength='10000' placeholder='Adicione um coment&#225;rio sobre esta resposta (opcional).'><?= esc($savedComment) ?></textarea>
            <div class='form-text'>O coment&#225;rio ser&#225; salvo junto com a resposta. (opcional)</div>
        </div>
        <?= view('application/types/questionnaire_evidence', ['q' => $q]) ?>
    </div>
</div>
