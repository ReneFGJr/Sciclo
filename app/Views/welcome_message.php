<?php
echo view('layout/header');
echo view('layout/navbar');
?>

<!-- Hero Section -->
<?php echo view('welcome_brand'); ?>

<?php if (session('logged_in')): ?>
<section class="container mb-4" aria-labelledby="my-submissions-title">
    <h2 class="h5 mb-3" id="my-submissions-title">Minhas submissões</h2>
    <div class="row g-3">
        <?php foreach (['draft' => 'Em submissão', 'submitted' => 'Em avaliação'] as $stage => $label): ?>
            <div class="col-sm-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <span class="fs-3 fw-bold me-2"><?= (int) $repositoryStatusCounts[$stage] ?></span>
                            <span><?= esc($label) ?></span>
                            <span class="small text-muted d-block">repositórios</span>
                        </div>
                        <a class="small" href="<?= site_url('profile') ?>#my-repositories" aria-label="<?= esc('Ver minhas submissões ' . mb_strtolower($label), 'attr') ?>">Ver detalhes <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <p class="small text-muted mt-2 mb-0">Acompanhe o preenchimento e a avaliação dos repositórios vinculados à sua conta.</p>
</section>
<?php endif; ?>

<?php echo view('seals/brand_seals', ['seals' => $seals]); ?>

<?php echo view('seals/seal_avaliation'); ?>

<?php echo view('seals/seal_statistics', ['totalRepositorios' => $totalRepositorios]); ?>

<?php echo view('layout/footer'); ?>
