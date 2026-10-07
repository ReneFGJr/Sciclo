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
        $data['repositoryStatusCounts'] = [];
        foreach (['draft', 'submitted'] as $stage) {
            $data['repositoryStatusCounts'][$stage] = 0;
            if (!session('logged_in') || !(int) session('user_id')) {
                continue;
            }
            $query = $Oai->where('submitted_by', (int) session('user_id'))->groupStart()
                ->where('seal_data_avaliation', null)
                ->orWhere('seal_data_avaliation', '0000-00-00 00:00:00')
                ->groupEnd();
            if ($stage === 'draft') {
                $query->groupStart()->where('submitted_at', null)
                    ->orWhere('submitted_at', '0000-00-00 00:00:00')->groupEnd();
            } else {
                $query->where('submitted_at !=', null)->where('submitted_at !=', '0000-00-00 00:00:00');
            }
            $data['repositoryStatusCounts'][$stage] = $query->countAllResults();
        }
        return view('welcome_message', $data);
    }
}
