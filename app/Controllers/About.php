<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class About extends Controller
{
    public function about_project()
    {
        return view('about/about_project');
    }

    public function contact()
    {
        return view('about/contact');
    }

    public function certification()
    {
        $Seals = new \App\Models\Seal\SealModel();
        $data['seals'] = $Seals->orderBy('id', 'DESC')->findAll();
        return view('about/certification', $data);
    }

    public function faq()
    {
        $model = new \App\Models\FaqQuestionModel();
        $data['faqs'] = $model->findAll();
        return view('about/faq', $data);
    }

    public function glossary()
    {
        $model = new \App\Models\GlossarioModel();
        $data['items'] = $model->orderBy('termo', 'ASC')->findAll();
        return view('about/glossary', $data);
    }
}
