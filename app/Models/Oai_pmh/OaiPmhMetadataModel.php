<?php
namespace App\Models\Oai_pmh;

use CodeIgniter\Model;

class OaiPmhMetadataModel extends Model
{
    protected $table = 'oai_pmh_metadata';
    protected $primaryKey = 'id';
    protected $allowedFields = ['oai_pmh_id', 'metadata_prefix', 'schema_url', 'metadata_namespace'];
    protected $useTimestamps = true;
}
