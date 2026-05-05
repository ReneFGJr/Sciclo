<?php
/**
 * View de componente para exibição de mensagens de status.
 * Parâmetros esperados:
 * - $status: int (200, 404, 500)
 * - $message: string
 */

$statusClass = [
    200 => 'success',
    404 => 'error',
    500 => 'danger',
][$status] ?? 'info';

$statusIcon = [
    'success' => '✔️',
    'error'   => '❌',
    'danger'  => '⚠️',
    'info'    => 'ℹ️',
][$statusClass];
?>
<div class="alert alert-<?= $statusClass ?>" role="alert" style="display: flex; align-items: center; gap: 8px;">
    <span><?= $statusIcon ?></span>
    <span><?= esc($message) ?></span>
</div>
