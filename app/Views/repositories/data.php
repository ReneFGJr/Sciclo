<?php
$submitter = $submitter ?? null;
$display = static fn ($value) => trim((string) ($value ?? '')) !== '' ? (string) $value : 'Não informado';
$formatDate = static function ($value) {
    if (empty($value) || str_starts_with((string) $value, '0000-00-00')) return 'Não informado';
    $timestamp = strtotime((string) $value);
    return $timestamp !== false ? date('d/m/Y H:i', $timestamp) : 'Não informado';
};
$groups = [
    'Identificação e contato' => ['repository_name' => 'Nome do repositório', 'base_url' => 'Site do repositório', 'base_url_oai' => 'Endpoint OAI-PMH', 'admin_email' => 'E-mail de contato do repositório'],
    'Informações técnicas' => ['repository_software_version' => 'Versão do software', 'protocol_version' => 'Versão do protocolo OAI-PMH', 'earliest_datestamp' => 'Registro mais antigo', 'deleted_record' => 'Política de registros excluídos', 'granularity' => 'Granularidade', 'compression' => 'Compressão'],
];
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div><h2 class="h4 mb-1">Dados do repositório</h2><p class="text-muted mb-0">Identificação, informações técnicas e responsável pela submissão.</p></div>
    <span class="badge bg-light text-dark border px-3 py-2">Identificador #<?= (int) $repository['id'] ?></span>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <section class="card shadow-sm mb-4" aria-labelledby="repository-overview-title">
            <div class="card-body p-4">
                <h3 class="h6 text-muted mb-3" id="repository-overview-title">Visão geral</h3>
                <div class="row g-4">
                    <div class="col-sm-6"><div class="small text-muted mb-2">Situação atual</div><span class="badge bg-success text-wrap text-start"><?= esc($status) ?></span></div>
                    <div class="col-sm-6"><div class="small text-muted mb-2">Tipo de repositório</div><div class="fw-semibold"><?= esc($types[$type] ?? $display($type)) ?></div></div>
                </div>
            </div>
        </section>
        <?php foreach ($groups as $heading => $groupFields): ?>
            <section class="card shadow-sm mb-4">
                <div class="card-header bg-light py-3 px-4"><h3 class="h5 mb-0"><?= esc($heading) ?></h3></div>
                <div class="card-body px-4 py-0">
                    <dl class="mb-0">
                        <?php foreach ($groupFields as $field => $label):
                            $value = trim((string) ($repository[$field] ?? ''));
                            $isUrl = in_array($field, ['base_url', 'base_url_oai'], true)
                                && filter_var($value, FILTER_VALIDATE_URL)
                                && in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true);
                        ?>
                            <div class="row border-bottom py-3 g-2">
                                <dt class="col-md-5 small text-muted fw-normal"><?= esc($label) ?></dt>
                                <dd class="col-md-7 mb-0 text-break">
                                    <?php if ($isUrl): ?>
                                        <a href="<?= esc($value, 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($value) ?> <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a>
                                    <?php elseif ($field === 'admin_email' && filter_var($value, FILTER_VALIDATE_EMAIL)): ?>
                                        <a href="mailto:<?= esc($value, 'attr') ?>"><?= esc($value) ?></a>
                                    <?php else: ?>
                                        <span class="<?= $value === '' ? 'text-muted' : '' ?>"><?= nl2br(esc($display($value))) ?></span>
                                    <?php endif; ?>
                                </dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
    <aside class="col-lg-4">
        <section class="card shadow-sm mb-4" aria-labelledby="repository-submitter-title">
            <div class="card-header bg-light py-3 px-4"><h3 class="h5 mb-0" id="repository-submitter-title"><i class="bi bi-person-circle me-2" aria-hidden="true"></i>Responsável pela submissão</h3></div>
            <div class="card-body p-4">
                <?php if ($submitter): ?>
                    <dl class="mb-0">
                        <dt class="small text-muted fw-normal mb-1">Nome</dt><dd class="fw-semibold text-break mb-3"><?= esc($display($submitter['name'])) ?></dd>
                        <dt class="small text-muted fw-normal mb-1">E-mail</dt><dd class="text-break mb-3">
                            <?php if (filter_var($submitter['email'] ?? '', FILTER_VALIDATE_EMAIL)): ?>
                                <a href="mailto:<?= esc($submitter['email'], 'attr') ?>"><?= esc($submitter['email']) ?></a>
                            <?php else: ?><?= esc($display($submitter['email'] ?? null)) ?><?php endif; ?>
                        </dd>
                        <dt class="small text-muted fw-normal mb-1">Identificador do usuário</dt><dd class="mb-0">#<?= (int) $submitter['id'] ?></dd>
                    </dl>
                <?php else: ?>
                    <p class="text-muted mb-0"><?= !empty($repository['submitted_by']) ? 'O cadastro do usuário responsável não está disponível.' : 'Nenhum usuário responsável foi registrado para este repositório.' ?></p>
                <?php endif; ?>
            </div>
        </section>
        <section class="card shadow-sm" aria-labelledby="repository-dates-title">
            <div class="card-header bg-light py-3 px-4"><h3 class="h5 mb-0" id="repository-dates-title"><i class="bi bi-clock-history me-2" aria-hidden="true"></i>Datas e acompanhamento</h3></div>
            <div class="card-body p-4"><dl class="mb-0">
                <?php foreach (['created_at' => 'Cadastro do repositório', 'updated_at' => 'Última atualização', 'submitted_at' => 'Envio para avaliação', 'seal_data_avaliation' => 'Avaliação concluída'] as $field => $label): ?>
                    <dt class="small text-muted fw-normal mb-1"><?= esc($label) ?></dt><dd class="mb-3"><?= esc($formatDate($repository[$field] ?? null)) ?></dd>
                <?php endforeach; ?>
            </dl></div>
        </section>
    </aside>
</div>
