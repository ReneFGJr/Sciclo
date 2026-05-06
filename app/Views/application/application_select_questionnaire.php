<?php
echo view('layout/header');
echo view('layout/navbar');
?>
<div class="container py-5">
    <?php $selectedType = old('questionario', $repo['repository_type'] ?? ''); ?>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="fw-bold mb-4">Selecione o Questionário de Certificação</h1>
            <p class="mb-4">Escolha abaixo o tipo de questionário que deseja responder para iniciar o processo de avaliação do seu repositório.</p>
            <form method="post" action="<?= base_url('application/form/select/' . $repo['id']); ?>">
                <div class="mb-4">
                    <label for="questionario" class="form-label">Tipo de Questionário</label>
                    <select class="form-select form-select-lg" id="questionario" name="questionario" required>
                        <option value="" <?= $selectedType === '' ? 'selected' : ''; ?> disabled>Selecione...</option>
                        <option value="publicacao" <?= $selectedType === 'publicacao' ? 'selected' : ''; ?>>Certificação de Repositório de Publicação</option>
                        <option value="dados" <?= $selectedType === 'dados' ? 'selected' : ''; ?>>Certificação de Repositório de Dados</option>
                        <option value="ambos" <?= $selectedType === 'ambos' ? 'selected' : ''; ?>>Certificação de Repositório de Publicação e Dados</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-lg px-5">Iniciar</button>
            </form>
        </div>
    </div>
</div>
<?php
echo view('layout/footer');
?>