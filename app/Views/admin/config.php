<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<div class="container my-4">
<?= view('admin/messages') ?>
<h2>Configurações</h2>
<div class="row g-3 my-4">
    <?php foreach ([
        'admin/users' => 'Usuários',
        'admin/questions' => 'Editar questões',
        'admin/glossario' => 'Editar glossário',
        'admin/faq' => 'Editar FAQ',
    ] as $path => $label): ?>
        <div class="col-md-4">
            <a class="card shadow-sm h-100 text-decoration-none" href="<?= site_url($path) ?>">
                <div class="card-body p-4">
                    <h3 class="h5 mb-0"><?= esc($label) ?></h3>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<nav class="nav nav-pills mb-4" aria-label="Configurações"><a class="nav-link" href="<?= base_url('admin/config/rules') ?>">Perfis de usuários</a></nav>
<p>Cadastre e gerencie os perfis disponíveis para atribuição aos usuários.</p>
</div>
<?= view('layout/footer') ?>
