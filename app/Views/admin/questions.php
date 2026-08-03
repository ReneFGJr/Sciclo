<?php
// app/Views/admin/questions.php
?>
<?= view('layout/header') ?>
<?= view('layout/navbar') ?>

<?php
$questions = isset($questions) && is_array($questions) ? $questions : [];
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0">Questões de Certificação</h1>
        <a href="<?= base_url('admin/questions/add') ?>" class="btn btn-success btn-lg">
            <i class="bi bi-plus-circle me-2"></i> Adicionar Questão
        </a>
    </div>
    <table class="table table-bordered table-hover align-middle bg-white">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Critério</th>
                <th>Eixo</th>
                <th>Nível 2</th>
                <th>Nível 3</th>
                <th>Questão/Informação</th>
                <th>Tipo de Resposta</th>
                <th>Descrição</th>
                <th>Ícone</th>
                <th>Imagem</th>
                <th>Condicional 1</th>
                <th>Condicional 2</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($questions as $q): ?>
            <tr>
                <td><?= $q['id'] ?></td>
                <td><?= esc($q['criterio']) ?></td>
                <td><?= esc($q['nivel1']) ?></td>
                <td><?= esc($q['nivel2']) ?></td>
                <td><?= esc($q['nivel3']) ?></td>
                <td><?= nl2br(glossario_conteudo($q['questao'] ?? '')) ?></td>
                <td><?= esc($q['tipo_resposta']) ?></td>
                <td><?= nl2br(glossario_conteudo($q['descricao'] ?? '')) ?></td>
                <td><?= esc($q['icone']) ?></td>
                <td><?php if ($q['imagem']): ?><img src="<?= base_url($q['imagem']) ?>" alt="Imagem" style="max-width:40px;max-height:40px;"/><?php endif; ?></td>
                <td><?= esc($q['condicional_1']) ?></td>
                <td><?= esc($q['condicional_2']) ?></td>
                <td class="text-center">
                    <a href="<?= base_url('admin/questions/edit/' . $q['id']) ?>" class="btn btn-sm btn-primary me-1" title="Editar">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="<?= base_url('admin/questions/delete/' . $q['id']) ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta questão?');">
                        <i class="bi bi-trash"></i>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= view('layout/footer') ?>
