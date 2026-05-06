<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">FAQ - Perguntas Cadastradas</h2>
        <a href="<?= base_url('/admin/faq/create') ?>" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Incluir nova questão
        </a>
    </div>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Pergunta</th>
                <th>Resposta</th>
                <th>Eixo</th>
                <th style="width:140px">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($faqs)): ?>
                <?php foreach ($faqs as $faq): ?>
                    <tr>
                        <td><?= esc($faq['id']) ?></td>
                        <td><?= esc($faq['question']) ?></td>
                        <td><?= esc($faq['answer']) ?></td>
                        <td><?= esc($faq['axis']) ?></td>
                        <td>
                            <a href="<?= base_url('/admin/faq/edit/' . $faq['id']) ?>" class="btn btn-sm btn-primary me-1" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="<?= base_url('/admin/faq/delete/' . $faq['id']) ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta questão?');">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5">Nenhuma pergunta cadastrada.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?= view('layout/footer') ?>
