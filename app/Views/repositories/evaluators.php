<h2 class="h4 mb-3">Avaliadores</h2>
<?php foreach (['evaluator_error' => 'danger', 'evaluator_success' => 'success'] as $key => $color): ?>
    <?php if (session()->getFlashdata($key)): ?><div class="alert alert-<?= $color ?>" role="alert"><?= esc(session()->getFlashdata($key)) ?></div><?php endif; ?>
<?php endforeach; ?>
<section class="card bg-light border-0 mb-4"><div class="card-body">
    <h3 class="h5">Indicar avaliador &#8212; rodada <?= (int) $currentRound ?></h3>
    <?php if ($availableEvaluators): ?>
        <form method="post" action="<?= site_url('repositories/' . (int) $repository['id'] . '/evaluators') ?>" class="row g-3 align-items-end">
            <?= csrf_field() ?>
            <div class="col-md-9">
                <label for="repository-evaluator" class="form-label">Avaliador</label>
                <select name="evaluator_id" id="repository-evaluator" class="form-select" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($availableEvaluators as $evaluator): ?><option value="<?= (int) $evaluator['id'] ?>"><?= esc($evaluator['name'] . ' - ' . $evaluator['email']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3"><button class="btn btn-primary w-100" type="submit">Indicar avaliador</button></div>
        </form>
        <p class="small text-muted mt-2 mb-0">S&#227;o listados os usu&#225;rios com perfil de avaliador vigente que ainda n&#227;o foram indicados nesta rodada.</p>
    <?php else: ?>
        <p class="text-muted mb-0">Nenhum avaliador dispon&#237;vel. Verifique os perfis e seus per&#237;odos de vig&#234;ncia no cadastro de usu&#225;rios.</p>
    <?php endif; ?>
</div></section>
<?php if ($evaluations): ?>
    <div class="table-responsive"><table class="table table-striped align-top">
        <thead><tr><th>Avaliador</th><th>Rodada</th><th>Situa&#231;&#227;o</th><th>Indicado em</th><th>Indicado por</th></tr></thead>
        <tbody><?php foreach ($evaluations as $evaluation): ?>
            <tr><td><?= esc($evaluation['evaluator_name']) ?><br><small class="text-muted"><?= esc($evaluation['evaluator_email']) ?></small></td>
                <td><?= (int) $evaluation['round'] ?></td>
                <td><?= esc(\App\Models\RepositoryEvaluationModel::STATUSES[$evaluation['status']] ?? $evaluation['status']) ?></td>
                <td><?= esc(date('d/m/Y H:i', strtotime($evaluation['assigned_at']))) ?></td>
                <td><?= esc($evaluation['assigner_name'] ?? 'Nao informado') ?></td></tr>
        <?php endforeach; ?></tbody>
    </table></div>
<?php else: ?><p class="text-muted">Nenhum avaliador indicado para este reposit&#243;rio.</p><?php endif; ?>
