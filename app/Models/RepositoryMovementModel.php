<?php
namespace App\Models;

class RepositoryMovementModel extends \CodeIgniter\Model
{
    protected $table = 'repository_movements';
    protected $allowedFields = ['oai_pmh_id', 'actor_id', 'from_status', 'to_status', 'details', 'recipient', 'email_status', 'email_sent_at', 'created_at'];
}
