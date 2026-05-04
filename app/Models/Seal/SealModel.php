<?php
namespace App\Models\Seal;

use CodeIgniter\Model;

class SealModel extends Model
{
    protected $table = 'seals';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'image', 'description'];
    public $timestamps = false;
}
