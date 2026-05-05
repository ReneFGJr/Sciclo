<?php
echo view('layout/header');
echo view('layout/navbar');

$repo_link = 'https://cedapdados.ufrgs.br';
?>

<div class="container py-5 text-center">
    <h2 class="mb-4">Iniciar Avaliação do Repositório</h2>
    <form action="<?= base_url(); ?>/application" method="post" class="mx-auto" style="max-width: 500px; font-size: 22px;">
        <div class="mb-4">
            <label for="repo_link" class="form-label fs-4 fw-bold">Informe a URL do seu repositório:</label>
            <input type="url" value="<?= isset($repo_link) ? $repo_link : '' ?>" name="repo_link" id="repo_link" class="form-control form-control-lg text-center" placeholder="https://meurepositorio.org" required>
        </div>
        <button type="submit" class="btn btn-primary btn-lg px-5 py-3 fw-bold" style="font-size: 2rem;">Iniciar a avaliação</button>
    </form>
</div>

<?php
echo view('layout/footer');
