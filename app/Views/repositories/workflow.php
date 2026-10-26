<?php $state = \App\Libraries\RepositoryWorkflow::status($repository); $labels = \App\Libraries\RepositoryWorkflow::LABELS; ?>
<?php foreach (['workflow_success' => 'success', 'workflow_error' => 'warning'] as $key => $class): ?>
<?php if ($message = session()->getFlashdata($key)): ?><div class="alert alert-<?= $class ?>" role="alert"><?= esc($message) ?></div><?php endif ?>
<?php endforeach ?>
<section class="card border-0 shadow-sm mb-4" aria-labelledby="workflow-title"><div class="card-body p-4">
<div class="d-flex flex-wrap justify-content-between gap-3 mb-3"><h2 id="workflow-title" class="h4 mb-0">Andamento da avaliação</h2><span class="badge bg-info text-dark"><?= esc(!empty($repository['seal_data_avaliation']) && $repository['seal_data_avaliation'] !== '0000-00-00 00:00:00' ? 'Avaliado' : ($labels[$state] ?? $state)) ?></span></div>
<?php if (in_array($state, ['submitted', 'accepted'], true) && (empty($repository['seal_data_avaliation']) || $repository['seal_data_avaliation'] === '0000-00-00 00:00:00')): ?>
<div class="d-flex flex-wrap gap-2">
<?php if ($state === 'submitted'): ?><form method="post" action="<?= site_url('repositories/' . $repository['id'] . '/movement') ?>"><?= csrf_field() ?><input type="hidden" name="target" value="accepted"><button class="btn btn-success" type="submit"><i class="bi bi-check-circle me-2" aria-hidden="true"></i>Aceitar para avaliação</button></form><?php endif ?>
<button class="btn btn-outline-warning text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#revision-form" aria-expanded="false" aria-controls="revision-form">Devolver para a origem (completar dados)</button>
</div>
<div class="collapse <?= old('details') ? 'show' : '' ?>" id="revision-form"><form class="border rounded p-3 mt-3" method="post" action="<?= site_url('repositories/' . $repository['id'] . '/movement') ?>">
<?= csrf_field() ?><input type="hidden" name="target" value="revisions_required">
<label class="form-label fw-semibold" for="revision-details">Detalhes e orientações para a origem</label>
<textarea id="revision-details" name="details" class="form-control" rows="5" required maxlength="10000" placeholder="Descreva quais dados precisam ser completados e como realizar os ajustes."><?= esc(old('details', '')) ?></textarea>
<p class="small text-muted mt-2">A origem receberá estas orientações por e-mail e poderá corrigir e reenviar a submissão.</p>
<button class="btn btn-warning" type="submit">Devolver e enviar e-mail</button>
</form></div>
<?php elseif ($state === 'revisions_required'): ?><p class="text-muted mb-0">Aguardando complementação dos dados e reenvio pela origem.</p><?php endif ?>
<details class="mt-4"><summary class="fw-semibold">Histórico de movimentações (<?= count($movements) ?>)</summary>
<div class="table-responsive mt-3"><table class="table align-middle"><thead><tr><th>Data / responsável</th><th>Movimentação</th><th>Detalhes</th><th>E-mail</th></tr></thead><tbody>
<?php foreach ($movements as $movement): ?><tr>
<td class="text-nowrap"><?= esc(date('d/m/Y H:i:s', strtotime($movement['created_at']))) ?><br><small><?= esc($movement['actor_name'] ?? 'Sistema') ?></small></td>
<td><?= esc($labels[$movement['from_status']] ?? $movement['from_status']) ?> → <strong><?= esc($labels[$movement['to_status']] ?? $movement['to_status']) ?></strong></td>
<td style="min-width:220px;max-width:480px;overflow-wrap:anywhere"><?= nl2br(esc($movement['details'] ?? '')) ?></td>
<td><?= esc(['not_required' => 'Não se aplica', 'pending' => 'Pendente', 'sending' => 'Enviando', 'sent' => 'Enviado', 'failed' => 'Falha no envio'][$movement['email_status']] ?? '') ?><br><small><?= esc($movement['recipient'] ?? '') ?></small>
<?php if (in_array($movement['email_status'], ['pending', 'failed'], true)): ?><form method="post" action="<?= site_url('repositories/' . $repository['id'] . '/movements/' . $movement['id'] . '/retry') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-primary mt-2" type="submit">Reenviar e-mail</button></form><?php endif ?></td>
</tr><?php endforeach ?>
<?php if (!$movements): ?><tr><td colspan="4" class="text-muted">Nenhuma movimentação registrada.</td></tr><?php endif ?>
</tbody></table></div></details></div></section>
