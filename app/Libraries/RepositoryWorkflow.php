<?php
namespace App\Libraries;

class RepositoryWorkflow
{
    public const LABELS = ['draft' => 'Em processo de submissão', 'submitted' => 'Enviado para avaliação',
        'revisions_required' => 'Revisões necessárias', 'accepted' => 'Aceito para avaliação'];

    public static function status(array $repository): string
    {
        return $repository['workflow_status'] ?? (!empty($repository['submitted_at']) ? 'submitted' : 'draft');
    }

    public static function move(array $repository, string $target, ?int $actor, string $details = '', ?string $recipient = null): int
    {
        $from = self::status($repository);
        $allowed = ['draft' => ['submitted'], 'submitted' => ['accepted', 'revisions_required'],
            'accepted' => ['revisions_required'], 'revisions_required' => ['submitted']];
        if (!in_array($target, $allowed[$from] ?? [], true)
            || (!empty($repository['seal_data_avaliation']) && $repository['seal_data_avaliation'] !== '0000-00-00 00:00:00')) {
            throw new \DomainException('Esta movimentação não está disponível para a situação atual.');
        }
        $db = db_connect();
        $db->transBegin();
        try {
            $data = ['workflow_status' => $target, 'updated_at' => date('Y-m-d H:i:s')];
            if ($target === 'submitted') $data += ['submitted_at' => date('Y-m-d H:i:s'), 'status' => 2];
            $ok = $db->table('oai_pmh')->where('id', $repository['id'])
                ->where('workflow_status', $repository['workflow_status'] ?? null)->update($data);
            if (!$ok || $db->affectedRows() !== 1) throw new \DomainException('A situação foi alterada. Atualize a página e tente novamente.');
            $model = new \App\Models\RepositoryMovementModel();
            $id = $model->insert(['oai_pmh_id' => $repository['id'], 'actor_id' => $actor,
                'from_status' => $from, 'to_status' => $target, 'details' => $details,
                'recipient' => $recipient, 'email_status' => $recipient ? 'pending' : 'not_required', 'created_at' => date('Y-m-d H:i:s')]);
            if (!$id || !$db->transStatus()) throw new \RuntimeException('Não foi possível registrar a movimentação.');
            if (!$db->transCommit()) throw new \RuntimeException('Não foi possível confirmar a movimentação.');
            return (int) $id;
        } catch (\Throwable $error) {
            $db->transRollback();
            throw $error;
        }
    }

    public static function notify(int $movementId): bool
    {
        $model = new \App\Models\RepositoryMovementModel();
        $movement = $model->find($movementId);
        if (!$movement || $movement['to_status'] !== 'revisions_required' || $movement['email_status'] === 'sent') return false;
        // Claim this notification to prevent duplicate sends from concurrent requests.
        $db = db_connect();
        $db->table('repository_movements')->where('id', $movementId)->whereIn('email_status', ['pending', 'failed'])->update(['email_status' => 'sending']);
        if ($db->affectedRows() !== 1) return false;
        $sent = false;
        try {
            $repository = (new \App\Models\Oai_pmh\OaiPmhModel())->find($movement['oai_pmh_id']);
            $mailer = new SystemEmail();
            $url = site_url('application/form/select/' . $repository['id']);
            $sent = $mailer->send($movement['recipient'],
                'Revisões necessárias — ' . ($repository['repository_name'] ?? 'Repositório'),
                'emails/repository_revision', compact('repository', 'movement', 'url'),
                "Sciclo — Revisões necessárias\n\n" . ($repository['repository_name'] ?? '') . "\n\n" . $movement['details'] . "\n\nAcesse sua conta, complete os dados e reenvie para avaliação: " . $url);

        } catch (\Throwable $error) {
            log_message('error', 'Falha na notificação da movimentação Sciclo {id}. Verifique as configurações de e-mail.', ['id' => $movementId]);
        }
        $model->update($movementId, ['email_status' => $sent ? 'sent' : 'failed', 'email_sent_at' => $sent ? date('Y-m-d H:i:s') : null]);
        return $sent;
    }
}
