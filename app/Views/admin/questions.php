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
    <form method="get" action="<?= site_url('admin/questions') ?>" class="row g-3 mb-4">
        <div class="col-md-5">
        <label for="question-level-filter" class="form-label fw-semibold">N&#237;vel de edi&#231;&#227;o</label>
        <select id="question-level-filter" name="nivel" class="form-select" onchange="this.form.requestSubmit()">
            <option value="">Todos os eixos e grupos</option>
            <?php foreach ($levelOptions ?? [] as $value => $option): ?>
                <option value="<?= esc($value, 'attr') ?>" <?= ($selectedLevel ?? '') === $value ? 'selected' : '' ?>><?= esc($option['label']) ?></option>
            <?php endforeach; ?>
        </select>
        </div>
        <div class="col-md-7">
            <label for="question-search" class="form-label fw-semibold">Buscar quest&#245;es</label>
            <div class="input-group">
                <input type="search" id="question-search" name="busca" class="form-control" value="<?= esc($search ?? '', 'attr') ?>" placeholder="Buscar por crit&#233;rio, quest&#227;o, descri&#231;&#227;o ou n&#250;mero...">
                <button type="submit" class="btn btn-outline-primary">Buscar</button>
            </div>
        </div>
    </form>
    <table class="table table-bordered table-hover align-top bg-white">
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
        <?php $currentLevel1 = null; $currentLevel2 = null; ?>
        <?php foreach ($questions as $q): ?>
            <?php
            $level1 = trim((string) ($q['nivel1'] ?? ''));
            $level2 = trim((string) ($q['nivel2'] ?? ''));
            $startsAxis = $level1 !== $currentLevel1;
            ?>
            <?php if ($startsAxis): ?>
                <tr class="table-primary">
                    <th colspan="13" class="py-3">N&#237;vel 1 &#8212; <?= $level1 !== '' ? 'Eixo ' . esc($level1) : 'Sem eixo definido' ?></th>
                </tr>
                <?php $currentLevel1 = $level1; $currentLevel2 = null; ?>
            <?php endif; ?>
            <?php if ($level2 !== $currentLevel2): ?>
                <tr class="table-secondary">
                    <th colspan="13"><?= $level2 !== '' ? 'N&#237;vel 2 &#8212; ' . esc($level1 . '.' . $level2) : 'Informa&#231;&#245;es gerais do eixo' ?></th>
                </tr>
                <?php $currentLevel2 = $level2; ?>
            <?php endif; ?>
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
                <td class="text-center align-top text-nowrap">
                    <a href="<?= base_url('admin/questions/edit/' . $q['id']) ?>" class="btn btn-sm btn-primary me-1" title="Editar">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="<?= base_url('admin/questions/delete/' . $q['id']) ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta questão?');">
                        <i class="bi bi-trash"></i>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($questions === []): ?>
            <tr><td colspan="13" class="text-center text-muted">Nenhuma quest&#227;o encontrada para os filtros selecionados.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?= view('layout/footer') ?>
