<?= view('layout/header') ?>
<?= view('layout/navbar') ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1">Editar Glossário</h1>
            <p class="text-muted mb-0">Gerencie os termos usados no sistema.</p>
        </div>
        <a href="<?= base_url('/admin/glossario/create') ?>" class="btn btn-success btn-lg">
            <i class="bi bi-plus-circle me-2"></i> Novo termo
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Termo</th>
                        <th>Definição</th>
                        <th>Data de atualização</th>
                        <th style="width:140px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= esc($item['id']) ?></td>
                                <td class="fw-semibold"><?= esc($item['termo']) ?></td>
                                <td><?= esc($item['definicao']) ?></td>
                                <td><?= esc($item['updated_at'] ?? '') ?></td>
                                <td>
                                    <a href="<?= base_url('/admin/glossario/edit/' . $item['id']) ?>" class="btn btn-sm btn-primary me-1" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= base_url('/admin/glossario/delete/' . $item['id']) ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este termo?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nenhum termo cadastrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('layout/footer') ?>
