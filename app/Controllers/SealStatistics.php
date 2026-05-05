<?php
namespace App\Controllers;

use App\Models\Oai_pmh\OaiPmhModel;
use App\Models\Seal\SealModel;
use CodeIgniter\Controller;

class SealStatistics extends Controller
{
    public function index()
    {
        $sealModel = new SealModel();
        $oaiModel = new OaiPmhModel();
        $data = [];
        $data['seals'] = $sealModel->findAll();
        $data['totalRepositorios'] = $oaiModel->totalRepositoriosAvaliados();
        return view('seals/seal_statistics', $data);
    }
}
