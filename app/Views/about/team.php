<?php
$groups = [
    'active' => ['title' => 'Mandatos ativos', 'status' => 'Vigente', 'badge' => 'bg-success', 'items' => []],
    'expired' => ['title' => 'Mandatos encerrados', 'status' => 'Encerrado', 'badge' => 'bg-secondary', 'items' => []],
    'scheduled' => ['title' => 'Mandatos agendados', 'status' => 'Agendado', 'badge' => 'bg-primary', 'items' => []],
];
foreach ($assignments as $assignment) {
    $key = $assignment['starts_at'] > $today ? 'scheduled' : ($assignment['ends_at'] < $today ? 'expired' : 'active');
    $groups[$key]['items'][] = $assignment;
}
?>
<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="fw-bold mb-3">Equipe</h1>
            <p class="text-muted mb-4">Conheça os integrantes da equipe, seus perfis e os períodos de seus mandatos.</p>
            <?php if (!$assignments): ?>
                <div class="alert alert-info">Nenhum integrante com perfil atribuído no momento.</div>
            <?php else: ?>
                <?php foreach ($groups as $key => $group): ?>
                    <?php if (!$group['items']) { continue; } ?>
                <section class="mb-5" aria-labelledby="mandates-<?= $key ?>">
                <h2 class="h4 mb-3" id="mandates-<?= $key ?>"><?= esc($group['title']) ?></h2>
                <div class="card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <caption class="px-3"><?= esc($group['title']) ?> da equipe.</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Nome</th>
                                    <th scope="col">Perfil</th>
                                    <th scope="col">Início do mandato</th>
                                    <th scope="col">Fim do mandato</th>
                                    <th scope="col">Situação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($group['items'] as $assignment): ?>
                                    <tr>
                                        <th scope="row"><?= esc($assignment['user_name']) ?></th>
                                        <td><?= esc($assignment['rule_name']) ?></td>
                                        <td><time datetime="<?= esc($assignment['starts_at'], 'attr') ?>"><?= esc(date('d/m/Y', strtotime($assignment['starts_at']))) ?></time></td>
                                        <td><time datetime="<?= esc($assignment['ends_at'], 'attr') ?>"><?= esc(date('d/m/Y', strtotime($assignment['ends_at']))) ?></time></td>
                                        <td><span class="badge <?= $group['badge'] ?>"><?= esc($group['status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                </section>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>
<?= view('layout/footer') ?>
