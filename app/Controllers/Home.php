<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $Seals = new \App\Models\Seal\SealModel();
        $Oai = new \App\Models\Oai_pmh\OaiPmhModel();
        $data['seals'] = $Seals->findAll();
        $data['totalRepositorios'] = $Oai->totalRepositoriosAvaliados();
        return view('welcome_message', $data);
    }
}
