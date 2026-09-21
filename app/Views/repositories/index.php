<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<main class="container py-5">
    <h1 class="fw-bold mb-3">Repositórios (Gestão)</h1>
    <p class="text-muted mb-4">Acompanhe os repositórios por etapa de submissão e avaliação.</p>
    <?php foreach ($groups as $key => $group): ?>
    <section class="mb-5" aria-labelledby="repositories-<?= $key ?>">
        <h2 class="h4 mb-3" id="repositories-<?= $key ?>"><?= esc($group['title']) ?>
            <span class="badge <?= $group['badge'] ?>"><?= count($group['items']) ?></span>
        </h2>
        <?php if (!$group['items']): ?>
            <p class="text-muted">Nenhum repositório nesta etapa.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead><tr><th scope="col">Repositório</th><th scope="col">URL</th><th scope="col">Tipo de repositório</th><th scope="col">Data de cadastro</th><th scope="col">Última movimentação</th><th scope="col">Enviado em</th><th scope="col">Avaliado em</th><th scope="col">Ações</th></tr></thead>
                <tbody>
                <?php foreach ($group['items'] as $repository): ?>
                    <tr>
                        <th scope="row"><?= esc($repository['repository_name'] ?: 'Repositório #' . $repository['id']) ?></th>
                        <td>
                            <?php if (filter_var($repository['base_url'], FILTER_VALIDATE_URL) && in_array(strtolower((string) parse_url($repository['base_url'], PHP_URL_SCHEME)), ['http', 'https'], true)): ?>
                                <a href="<?= esc($repository['base_url'], 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($repository['base_url']) ?></a>
                            <?php else: ?>
                                <?= esc($repository['base_url']) ?>
                            <?php endif; ?>
                        </td>
                        <?php
                        $type = $repository['repository_type'] ?? '';
                        $types = ['publicacao' => 'Publicação', 'dados' => 'Dados', 'ambos' => 'Publicação e Dados', 'arquivistico' => 'Arquivístico'];
                        ?>
                        <td><?= esc($types[$type] ?? ($type !== '' ? $type : 'Não informado')) ?></td>
                        <?php foreach (['created_at', 'updated_at', 'submitted_at', 'seal_data_avaliation'] as $field): ?>
                        <td><?= !empty($repository[$field]) && $repository[$field] !== '0000-00-00 00:00:00' ? esc(date('d/m/Y H:i', strtotime($repository[$field]))) : '—' ?></td>
                        <?php endforeach; ?>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="<?= base_url('repositories/' . $repository['id']) ?>" title="Ver dados do repositório" aria-label="Ver dados do repositório <?= esc($repository['repository_name'] ?: '#' . $repository['id'], 'attr') ?>">
                                <i class="bi bi-eye" aria-hidden="true"></i> Ver
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>
    <?php endforeach; ?>
</main>
<?= view('layout/footer') ?>