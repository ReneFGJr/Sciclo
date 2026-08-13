<?= view('layout/header') ?>
<?= view('layout/navbar') ?>

<div class="container py-5">
    <div class="mb-4">
        <h1 class="fw-bold mb-1">Glossário</h1>
        <p class="text-muted mb-0">Termos e definições utilizados no sistema.</p>
    </div>

    <?php if (!empty($items)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Termo</th>
                        <th scope="col">Definição</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <th scope="row"><?= esc($item['termo']) ?></th>
                            <td><?= nl2br(esc($item['definicao'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">Nenhum termo cadastrado.</p>
    <?php endif; ?>
</div>

<?= view('layout/footer') ?>