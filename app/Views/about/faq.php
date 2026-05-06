<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<div class="container mt-4">
    <h2>FAQ - Perguntas Frequentes</h2>
    <div class="accordion mt-4" id="faqAccordion">
        <?php if (!empty($faqs)): ?>
            <?php foreach ($faqs as $i => $faq): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?= $i ?>">
                        <button class="accordion-button <?= $i !== 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $i ?>" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $i ?>">
                            <?= esc($faq['question']) ?>
                            <?php if (!empty($faq['axis'])): ?>
                                <span class="badge bg-secondary ms-2">Eixo: <?= esc($faq['axis']) ?></span>
                            <?php endif; ?>
                        </button>
                    </h2>
                    <div id="collapse<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $i ?>" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <?= str_replace("\n", "<br><br>", esc($faq['answer'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">Nenhuma pergunta cadastrada.</p>
        <?php endif; ?>
    </div>
</div>
<?= view('layout/footer') ?>
