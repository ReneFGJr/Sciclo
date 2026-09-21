<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<div class="container my-4">
<?= view('admin/messages') ?>
<h2>Configurações</h2>
<nav class="nav nav-pills mb-4" aria-label="Configurações"><a class="nav-link" href="<?= base_url('admin/config/rules') ?>">Perfis de usuários</a></nav>
<p>Cadastre e gerencie os perfis disponíveis para atribuição aos usuários.</p>
</div>
<?= view('layout/footer') ?>