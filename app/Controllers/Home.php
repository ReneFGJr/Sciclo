<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $Seals = new \App\Models\seal\SealModel();
        $data['seals'] = $Seals->findAll();
        return view('welcome_message', $data);
    }
}
