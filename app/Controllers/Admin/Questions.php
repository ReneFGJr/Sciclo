<?php
namespace App\Controllers\Admin;

use App\Models\Question\CertificacaoQuestoesModel;
use CodeIgniter\Controller;

class Questions extends Controller

{
    public function index()
    {
        $model = new CertificacaoQuestoesModel();
        $questions = $model->findAll();

        usort($questions, static function (array $a, array $b): int {
            foreach (['nivel1', 'nivel2', 'nivel3', 'criterio'] as $field) {
                $cmp = strnatcmp((string) ($a[$field] ?? ''), (string) ($b[$field] ?? ''));
                if ($cmp !== 0) {
                    return $cmp;
                }
            }

            return (int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0);
        });

        $levelOptions = [];
        foreach ($questions as $question) {
            $level1 = trim((string) ($question['nivel1'] ?? ''));
            $level2 = trim((string) ($question['nivel2'] ?? ''));
            if ($level1 === '') continue;
            $axisKey = 'axis:' . rawurlencode($level1);
            $levelOptions[$axisKey] = ['label' => 'Eixo ' . $level1 . ' - Todos os grupos', 'nivel1' => $level1, 'nivel2' => null];
            if ($level2 !== '') {
                $groupKey = 'group:' . rawurlencode($level1) . ':' . rawurlencode($level2);
                $levelOptions[$groupKey] = ['label' => 'Nível ' . $level1 . '.' . $level2, 'nivel1' => $level1, 'nivel2' => $level2];
            }
        }
        $requestedLevel = service('request')->getGet('nivel');
        $selectedLevel = is_string($requestedLevel) && isset($levelOptions[$requestedLevel]) ? $requestedLevel : '';
        if ($selectedLevel !== '') {
            $filter = $levelOptions[$selectedLevel];
            $questions = array_values(array_filter($questions, static function (array $question) use ($filter): bool {
                return trim((string) ($question['nivel1'] ?? '')) === $filter['nivel1']
                    && ($filter['nivel2'] === null || trim((string) ($question['nivel2'] ?? '')) === $filter['nivel2']);
            }));
        }
        $requestedSearch = service('request')->getGet('busca');
        $search = is_string($requestedSearch) ? trim($requestedSearch) : '';
        if ($search !== '') {
            $normalize = static function (string $text): string {
                $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                return mb_strtolower(transliterator_transliterate('Any-Latin; Latin-ASCII', $text) ?: $text, 'UTF-8');
            };
            $term = $normalize($search);
            $questions = array_values(array_filter($questions, static function (array $question) use ($normalize, $term): bool {
                $values = [];
                foreach (['id', 'criterio', 'questao', 'descricao', 'tipo_resposta'] as $field) {
                    $values[] = (string) ($question[$field] ?? '');
                }
                $values[] = implode('.', [$question['nivel1'] ?? '', $question['nivel2'] ?? '', $question['nivel3'] ?? '']);
                return str_contains($normalize(implode(' ', $values)), $term);
            }));
        }
        $data['search'] = $search;
        $data['levelOptions'] = $levelOptions;
        $data['selectedLevel'] = $selectedLevel;
        $data['questions'] = $questions;
        return view('admin/questions', $data);
    }

    public function edit($id)
    {
        $model = new CertificacaoQuestoesModel();
        $request = service('request');
        $question = $model->find($id);
        if (!$question) {
            return redirect()->to(base_url('admin/questions'));
        }
        $method = strtolower($request->getMethod());
        if ($method === 'post') {
            if (!$this->validConditionalTargets($model, isset($id) ? (int) $id : null)) {
                return redirect()->back()->withInput()->with('question_error', 'Selecione uma questão de destino válida, do mesmo critério e diferente da questão atual.');
            }
            $data = [
                'criterio' => $request->getPost('criterio'),
                'nivel1' => $request->getPost('nivel1'),
                'nivel2' => $request->getPost('nivel2'),
                'nivel3' => $request->getPost('nivel3'),
                'questao' => $request->getPost('questao'),
                'tipo_resposta' => $request->getPost('tipo_resposta'),
                'descricao' => $request->getPost('descricao'),
                'feedback' => $request->getPost('feedback'),
                'icone' => $request->getPost('icone'),
                'imagem' => $request->getPost('imagem'),
                'condicional_1' => (int) $request->getPost('condicional_1'),
                'condicional_2' => (int) $request->getPost('condicional_2'),
            ];
            $model->update($id, $data);
            return redirect()->to(base_url('admin/questions'));
        }
        return view('admin/add_question', ['question' => $question, 'targetQuestions' => $model->orderBy('criterio', 'ASC')->orderBy('id', 'ASC')->findAll()]);
    }

    public function delete($id)
    {
        $model = new CertificacaoQuestoesModel();
        $model->delete($id);
        return redirect()->to(base_url('admin/questions'));
    }

    public function add()
    {
        $model = new CertificacaoQuestoesModel();
        $request = service('request');
        $method = strtolower($request->getMethod());
        if ($method === 'post') {
            if (!$this->validConditionalTargets($model, isset($id) ? (int) $id : null)) {
                return redirect()->back()->withInput()->with('question_error', 'Selecione uma questão de destino válida, do mesmo critério e diferente da questão atual.');
            }
            $data = [
                'criterio' => $request->getPost('criterio'),
                'nivel1' => $request->getPost('nivel1'),
                'nivel2' => $request->getPost('nivel2'),
                'nivel3' => $request->getPost('nivel3'),
                'questao' => $request->getPost('questao'),
                'tipo_resposta' => $request->getPost('tipo_resposta'),
                'descricao' => $request->getPost('descricao'),
                'feedback' => $request->getPost('feedback'),
                'icone' => $request->getPost('icone'),
                'imagem' => $request->getPost('imagem'),
                'condicional_1' => (int) $request->getPost('condicional_1'),
                'condicional_2' => (int) $request->getPost('condicional_2'),
            ];
            $model->insert($data);
            return redirect()->to(base_url('admin/questions'));
        }
        return view('admin/add_question', ['targetQuestions' => $model->orderBy('criterio', 'ASC')->orderBy('id', 'ASC')->findAll()]);
    }
    private function validConditionalTargets(CertificacaoQuestoesModel $model, ?int $currentId): bool
    {
        foreach (['condicional_1', 'condicional_2'] as $field) {
            $value = service('request')->getPost($field);
            if ($value === null || $value === '' || $value === '0') {
                continue;
            }
            $targetId = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($targetId === false || $targetId === $currentId) {
                return false;
            }
            $target = $model->find($targetId);
            if (!$target || (string) $target['criterio'] !== (string) service('request')->getPost('criterio')) {
                return false;
            }
        }

        return true;
    }
}
