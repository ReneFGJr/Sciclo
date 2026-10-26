<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<div class="container py-5">
    <div class="row justify-content-center"><div class="col-lg-9">
        <h1 class="fw-bold mb-4">Revisar submiss&#227;o</h1>
        <?php if (session()->getFlashdata('review_error')): ?>
            <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('review_error')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('review_success')): ?>
            <div class="alert alert-success" role="alert"><?= esc(session()->getFlashdata('review_success')) ?></div>
        <?php endif; ?>
        <section class="card bg-light border-0 mb-4"><div class="card-body">
            <h2 class="h5"><?= esc($repo['repository_name'] ?: 'Repositorio') ?></h2>
            <p class="text-break mb-2"><?= esc($repo['base_url']) ?></p>
            <strong><?= esc(number_format($progress['completion'], 1, ',', '.')) ?>% preenchido</strong>
            <p class="mb-0"><?= (int) $progress['answered'] ?> de <?= (int) $progress['total'] ?> quest&#245;es preenchidas; <?= (int) $progress['pending'] ?> pendentes.</p>
        </div></section>
        <div class="table-responsive mb-4"><table class="table align-middle">
            <thead><tr><th>Eixo</th><th class="text-end">Preenchidas</th><th class="text-end">Pendentes</th><th class="text-end">Percentual</th></tr></thead>
            <tbody><?php foreach ($progress['groups'] as $axis => $group): ?>
                <tr><th scope="row"><?= esc((string) $axis) ?> - <?= esc($progress['axis_titles'][$axis] ?? '') ?></th>
                    <td class="text-end"><?= (int) $group['answered'] ?> / <?= (int) $group['total'] ?></td>
                    <td class="text-end"><?= (int) $group['pending'] ?></td>
                    <td class="text-end"><?= esc(number_format($group['completion'], 1, ',', '.')) ?>%</td></tr>
            <?php endforeach; ?></tbody>
        </table></div>
        <?php if (!empty($repo['submitted_at']) && ($repo['workflow_status'] ?? '') !== 'revisions_required'): ?>
            <div class="alert alert-success">Este reposit&#243;rio j&#225; foi enviado para avalia&#231;&#227;o.</div>
            <a href="<?= site_url('application/form/select/' . (int) $repo['id']) ?>" class="btn btn-outline-primary">Voltar ao resumo</a>
        <?php else: ?>
            <form method="post" action="<?= site_url('application/review') ?>">
                <?= csrf_field() ?>
                <fieldset class="border rounded p-3 mb-4">
                    <legend class="float-none w-auto px-2 fs-5">Termo de confirma&#231;&#227;o</legend>
                    <p>Declaro que revisei as respostas e as evid&#234;ncias do reposit&#243;rio e que as informa&#231;&#245;es fornecidas s&#227;o corretas. Confirmo o envio desta submiss&#227;o para avalia&#231;&#227;o.</p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="confirm_submission" value="1" id="confirm-submission" required>
                        <label class="form-check-label" for="confirm-submission">Li e confirmo o termo acima.</label>
                    </div>
                </fieldset>
                <?php if ($progress['pending'] > 0): ?><p class="text-danger">Complete as respostas pendentes antes de enviar.</p><?php endif; ?>
                <div class="d-flex flex-wrap justify-content-between gap-3">
                    <a class="btn btn-outline-primary" href="<?= site_url('application/form/1') ?>">Revisar respostas</a>
                    <button class="btn btn-success" type="submit" <?= $progress['pending'] > 0 || $progress['total'] === 0 ? 'disabled' : '' ?>>Confirmar e enviar para avalia&#231;&#227;o</button>
                </div>
            </form>
        <?php endif; ?>
    </div></div>
</div>
<?= view('layout/footer') ?>
