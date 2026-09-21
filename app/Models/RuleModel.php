<?php
namespace App\Models;
use CodeIgniter\Model;
class RuleModel extends Model
{
    protected $table = 'rules';
    protected $allowedFields = ['name', 'code'];
}