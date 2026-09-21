<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class About extends Controller
{
    public function about_project()
    {
        return view('about/about_project');
    }

    public function team()
    {
        $assignments = (new \App\Models\UserRuleModel())
            ->select('users.name AS user_name, rules.name AS rule_name, user_rules.starts_at, user_rules.ends_at')
            ->join('users', 'users.id = user_rules.user_id')
            ->join('rules', 'rules.id = user_rules.rule_id')
            ->orderBy('users.name', 'ASC')
            ->orderBy('user_rules.starts_at', 'DESC')
            ->orderBy('rules.name', 'ASC')
            ->findAll();

        return view('about/team', ['assignments' => $assignments, 'today' => date('Y-m-d')]);
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
        $data['faqs'] = $model->orderBy('ordem', 'ASC')->orderBy('id', 'ASC')->findAll();
        return view('about/faq', $data);
    }

    public function glossary()
    {
        $model = new \App\Models\GlossarioModel();
        $data['items'] = $model->orderBy('termo', 'ASC')->findAll();
        return view('about/glossary', $data);
    }
}
