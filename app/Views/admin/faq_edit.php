<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<div class="container mt-4">
    <h2>Editar Questão do FAQ</h2>
    <form method="post" action="<?= base_url('/admin/faq/edit/' . $faq['id']) ?>">
        <div class="mb-3">
            <label for="question" class="form-label">Pergunta</label>
            <textarea class="form-control" id="question" name="question" rows="2" required><?= esc($faq['question']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="answer" class="form-label">Resposta</label>
            <textarea class="form-control" id="answer" name="answer" rows="3" required><?= esc($faq['answer']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="axis" class="form-label">Eixo</label>
            <input type="text" class="form-control" id="axis" name="axis" value="<?= esc($faq['axis']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Salvar</button>
        <a href="<?= base_url('/admin/faq') ?>" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<?= view('layout/footer') ?>
