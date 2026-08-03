<?= view('layout/header') ?>
<?= view('layout/navbar') ?>

<?php $item = isset($item) && is_array($item) ? $item : []; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="mb-4">
                        <h1 class="fw-bold mb-1"><?= esc($title ?? 'Glossário') ?></h1>
                        <p class="text-muted mb-0">Preencha o termo e a definição. A data de atualização é automática.</p>
                    </div>

                    <form method="post" action="<?= esc($action ?? '') ?>">
                        <div class="mb-3">
                            <label for="termo" class="form-label">Termo</label>
                            <input type="text" class="form-control form-control-lg" id="termo" name="termo" value="<?= esc($item['termo'] ?? '') ?>" required>
                        </div>
                        <div class="mb-4">
                            <label for="definicao" class="form-label">Definição</label>
                            <textarea class="form-control" id="definicao" name="definicao" rows="8" required><?= esc($item['definicao'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Salvar</button>
                            <a href="<?= base_url('/admin/glossario') ?>" class="btn btn-outline-secondary btn-lg">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('layout/footer') ?>
