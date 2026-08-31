<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<div class="container mt-4">
    <h2>Incluir Nova Questão no FAQ</h2>
    <form method="post" action="<?= base_url('/admin/faq/create') ?>">
        <div class="mb-3">
            <label for="question" class="form-label">Pergunta</label>
            <textarea class="form-control" id="question" name="question" rows="2" required></textarea>
        </div>
        <div class="mb-3">
            <label for="answer" class="form-label">Resposta</label>
            <textarea class="form-control" id="answer" name="answer" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label for="axis" class="form-label">Eixo</label>
            <input type="text" class="form-control" id="axis" name="axis" required>
        </div>
        <div class="mb-3">
            <label for="ordem" class="form-label">Ordem</label>
            <select class="form-select" id="ordem" name="ordem" required>
                <?php for ($ordem = 1; $ordem <= 100; $ordem++): ?>
                    <option value="<?= $ordem ?>"><?= $ordem ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Salvar</button>
        <a href="<?= base_url('/admin/faq') ?>" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<?= view('layout/footer') ?>
