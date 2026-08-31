<?php
namespace App\Models;

use CodeIgniter\Model;

class FaqQuestionModel extends Model
{
    protected $table = 'faq_questions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'question',
        'answer',
        'axis',
        'ordem',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
}
