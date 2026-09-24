<?php
namespace App\Models;

use CodeIgniter\Model;

class RepositoryEvaluationModel extends Model
{
    public const STATUSES = [
        'assigned' => 'Indicada',
        'accepted' => 'Aceita',
        'in_progress' => 'Em andamento',
        'completed' => 'Concluída',
        'declined' => 'Recusada',
        'cancelled' => 'Cancelada',
    ];
    public const RECOMMENDATIONS = [
        'approved' => 'Aprovar',
        'changes_requested' => 'Solicitar ajustes',
        'rejected' => 'Reprovar',
    ];

    protected $table = 'repository_evaluations';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'oai_pmh_id', 'evaluator_id', 'assigned_by', 'round', 'assigned_at',
        'viewed_at', 'accepted_at', 'due_at', 'completed_at', 'cancelled_at',
        'status', 'recommendation', 'report', 'notes', 'cancellation_reason',
    ];
    protected $validationRules = [
        'oai_pmh_id' => 'required|is_natural_no_zero',
        'evaluator_id' => 'required|is_natural_no_zero',
        'assigned_by' => 'permit_empty|is_natural_no_zero',
        'round' => 'if_exist|is_natural_no_zero',
        'assigned_at' => 'required|valid_date[Y-m-d H:i:s]',
        'viewed_at' => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'accepted_at' => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'due_at' => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'completed_at' => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'cancelled_at' => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'status' => 'if_exist|in_list[assigned,accepted,in_progress,completed,declined,cancelled]',
        'recommendation' => 'permit_empty|in_list[approved,changes_requested,rejected]',
    ];
}
