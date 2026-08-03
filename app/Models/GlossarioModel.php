<?php

namespace App\Models;

use CodeIgniter\Model;

class GlossarioModel extends Model
{
    protected $table = 'glossarios';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'termo',
        'definicao',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
}
