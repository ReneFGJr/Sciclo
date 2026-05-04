<?php
namespace App\Models\Oai_pmh;

use CodeIgniter\Model;

class OaiPmhModel extends Model
{
    protected $table = 'oai_pmh';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'image', 'description'];
    public $timestamps = false;

    function getIdentify($id)
        {

        }
}
