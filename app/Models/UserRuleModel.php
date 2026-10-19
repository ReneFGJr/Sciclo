<?php
namespace App\Models;
use CodeIgniter\Model;
class UserRuleModel extends Model
{
    protected $table = 'user_rules';
    protected $allowedFields = ['user_id', 'rule_id', 'starts_at', 'ends_at'];

    public function activeEvaluators(): array
    {
        return $this->select('users.id, users.name, users.email')->distinct()
            ->join('rules', 'rules.id = user_rules.rule_id')
            ->join('users', 'users.id = user_rules.user_id')
            ->where('rules.code', 'evaluator')
            ->where('starts_at <=', date('Y-m-d'))->where('ends_at >=', date('Y-m-d'))
            ->orderBy('users.name', 'ASC')->findAll();
    }

    public function isAdministrator(int $userId): bool
    {
        if (!$userId) {
            return false;
        }
        return $this->join('rules', 'rules.id = user_rules.rule_id')
            ->where('user_rules.user_id', $userId)->where('rules.code', 'site-manager')
            ->where('starts_at <=', date('Y-m-d'))->where('ends_at >=', date('Y-m-d'))
            ->countAllResults() > 0;
    }
}