<?php
// app/Views/admin/add_question.php
?>
<?= view('layout/header') ?>
<?= view('layout/navbar') ?>

<div class="container py-5">
    <h1 class="mb-4 fw-bold">Questão de Certificação</h1>
    <form method="post" action="">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="criterio" class="form-label">Critério</label>
                <input type="text" class="form-control" id="criterio" name="criterio" maxlength="15" required value="<?= isset($question) ? esc($question['criterio']) : '' ?>">
            </div>
            <div class="col-md-3">
                <label for="nivel1" class="form-label">Eixo de Certificação</label>
                <select class="form-select" id="nivel1" name="nivel1" required>
                    <option value="" disabled <?= !isset($question) ? 'selected' : '' ?>>Selecione...</option>
                    <option value="1" <?= (isset($question) && $question['nivel1'] == '1') ? 'selected' : '' ?>>1º Eixo</option>
                    <option value="2" <?= (isset($question) && $question['nivel1'] == '2') ? 'selected' : '' ?>>2º Eixo</option>
                    <option value="3" <?= (isset($question) && $question['nivel1'] == '3') ? 'selected' : '' ?>>3º Eixo</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="nivel2" class="form-label">Nível 2</label>
                <input type="text" class="form-control" id="nivel2" name="nivel2" value="<?= isset($question) ? esc($question['nivel2']) : '' ?>">
            </div>
            <div class="col-md-3">
                <label for="nivel3" class="form-label">Nível 3</label>
                <input type="text" class="form-control" id="nivel3" name="nivel3" value="<?= isset($question) ? esc($question['nivel3']) : '' ?>">
            </div>
        </div>
        <div class="mb-3 mt-3">
            <label for="questao" class="form-label">Questão/Informação</label>
            <textarea class="form-control" id="questao" name="questao" rows="2" required><?= isset($question) ? esc($question['questao']) : '' ?></textarea>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <label for="tipo_resposta" class="form-label">Tipo de Resposta</label>
                <select class="form-select" id="tipo_resposta" name="tipo_resposta" required>
                    <option value="" disabled <?= !isset($question) ? 'selected' : '' ?>>Selecione...</option>
                    <option value="SN" <?= (isset($question) && $question['tipo_resposta'] == 'SN') ? 'selected' : '' ?>>Sim ou Não</option>
                    <option value="TEXT" <?= (isset($question) && $question['tipo_resposta'] == 'TEXT') ? 'selected' : '' ?>>Texto longo</option>
                    <option value="SHORTEXT" <?= (isset($question) && $question['tipo_resposta'] == 'SHORTEXT') ? 'selected' : '' ?>>Texto curto</option>
                    <option value="URL" <?= (isset($question) && $question['tipo_resposta'] == 'URL') ? 'selected' : '' ?>>URL</option>
                    <option value="DOC" <?= (isset($question) && $question['tipo_resposta'] == 'DOC') ? 'selected' : '' ?>>Documento (upload)</option>
                    <option value="INFO" <?= (isset($question) && $question['tipo_resposta'] == 'INFO') ? 'selected' : '' ?>>Informação</option>
                    <option value="IMG" <?= (isset($question) && $question['tipo_resposta'] == 'IMG') ? 'selected' : '' ?>>Imagem</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="icone" class="form-label">Ícone</label>
                <input type="text" class="form-control" id="icone" name="icone" value="<?= isset($question) ? esc($question['icone']) : '' ?>">
            </div>
            <div class="col-md-4">
                <label for="imagem" class="form-label">Imagem (URL)</label>
                <input type="text" class="form-control" id="imagem" name="imagem" value="<?= isset($question) ? esc($question['imagem']) : '' ?>">
            </div>
        </div>
        <div class="mb-3 mt-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea class="form-control" id="descricao" name="descricao" rows="2"><?= isset($question) ? esc($question['descricao']) : '' ?></textarea>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="condicional_1" class="form-label">Condicional 1</label>
                <input type="number" class="form-control" id="condicional_1" name="condicional_1" value="<?= isset($question) ? esc($question['condicional_1']) : '0' ?>">
            </div>
            <div class="col-md-6">
                <label for="condicional_2" class="form-label">Condicional 2</label>
                <input type="number" class="form-control" id="condicional_2" name="condicional_2" value="<?= isset($question) ? esc($question['condicional_2']) : '0' ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-success btn-lg">Salvar</button>
        <a href="<?= base_url('admin/questions') ?>" class="btn btn-secondary btn-lg ms-2">Cancelar</a>
    </form>
</div>

<?= view('layout/footer') ?>