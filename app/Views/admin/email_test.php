<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<div class="container my-4">
    <a href="<?= site_url('admin') ?>" class="text-decoration-none">&larr; Voltar às configurações</a>
    <h2 class="mt-3">Testar e-mail</h2>
    <p class="text-muted">Envie uma mensagem de teste para verificar o recebimento dos e-mails do Sciclo.</p>
    <?= view('admin/messages') ?>
    <?php if (!empty($diagnostic)): ?>
        <section class="card border-danger mb-4" aria-labelledby="email-error-title">
            <div class="card-body">
                <h3 class="h5" id="email-error-title">Detalhes da falha no envio</h3>
                <pre class="bg-light border rounded p-3" style="white-space: pre-wrap; overflow-wrap: anywhere;"><?= esc($diagnostic['details']) ?></pre>
                <p class="mb-2"><strong>Configuração utilizada</strong></p>
                <dl class="row small mb-2">
                    <dt class="col-sm-3">Protocolo</dt><dd class="col-sm-9"><?= esc($diagnostic['protocol']) ?></dd>
                    <dt class="col-sm-3">Remetente</dt><dd class="col-sm-9"><?= esc($diagnostic['from'] ?: 'Não configurado') ?></dd>
                    <?php if ($diagnostic['protocol'] === 'smtp'): ?>
                        <dt class="col-sm-3">Servidor SMTP</dt><dd class="col-sm-9"><?= esc($diagnostic['host'] ?: 'Não configurado') ?></dd>
                        <dt class="col-sm-3">Porta</dt><dd class="col-sm-9"><?= esc($diagnostic['port']) ?></dd>
                        <dt class="col-sm-3">Criptografia</dt><dd class="col-sm-9"><?= esc($diagnostic['crypto'] ?: 'Sem criptografia explícita') ?></dd>
                    <?php endif; ?>
                </dl>
                <p class="small text-muted mb-0">Falha de conexão: confira servidor, porta e acesso à rede. Erro de autenticação (535): confira as credenciais e a senha de aplicativo, se exigida pelo provedor. Remetente ou destinatário rejeitado (550/553): confira os endereços e as permissões de envio. Erro de TLS/certificado: confira a criptografia e o certificado do servidor.</p>
            </div>
        </section>
    <?php endif; ?>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form id="email-test-form" method="post" action="<?= site_url('admin/email_test') ?>">
                        <?= csrf_field() ?>
                        <label class="form-label" for="email">E-mail do destinatário</label>
                        <input class="form-control mb-3" type="email" id="email" name="email" required maxlength="254" autocomplete="email" placeholder="nome@exemplo.com" value="<?= esc($recipient, 'attr') ?>" aria-describedby="email-help">
                        <p class="small text-muted" id="email-help">A mensagem ao lado será enviada para o endereço informado.</p>
                        <p class="small text-muted">O envio utiliza os dados do arquivo .env. Para Gmail, use EMAIL_TYPE=smtp, porta 465 e preencha EMAIL_FROM, EMAIL_USER_AUTH e EMAIL_PASSWORD com a senha de aplicativo. EMAIL_TYPE=sendmail utiliza o Sendmail local.</p>
                        <button id="email-test-submit" type="submit" class="btn btn-success w-100">
                            <span id="email-test-spinner" class="spinner-border spinner-border-sm me-2" aria-hidden="true" hidden></span>
                            <span id="email-test-label">Enviar teste</span>
                        </button>
                        <p id="email-test-status" class="small text-muted mt-3 mb-0" role="status" aria-live="polite"></p>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <h3 class="h5">Prévia do e-mail</h3>
            <iframe title="Prévia do e-mail de teste Sciclo" sandbox="" class="w-100 border rounded" style="height: 560px; background: #f0f5f3" srcdoc="<?= esc(view('emails/test', ['logoUrl' => base_url('assets/logo/logo_sciclo.png')]), 'attr') ?>"></iframe>
        </div>
    </div>
</div>
<script>
(() => {
    const form = document.getElementById('email-test-form');
    const button = document.getElementById('email-test-submit');
    const spinner = document.getElementById('email-test-spinner');
    const label = document.getElementById('email-test-label');
    const status = document.getElementById('email-test-status');
    const recipient = document.getElementById('email');
    let sending = false;

    form.addEventListener('submit', (event) => {
        if (sending) {
            event.preventDefault();
            return;
        }
        // Native validation runs before submit; keep the field in the POST data.
        sending = true;
        form.setAttribute('aria-busy', 'true');
        button.disabled = true;
        recipient.readOnly = true;
        spinner.hidden = false;
        label.textContent = 'Enviando…';
        status.textContent = 'Aguarde enquanto enviamos o e-mail de teste.';
    });

    // Restore the form when navigating back from the result page.
    window.addEventListener('pageshow', () => {
        sending = false;
        form.removeAttribute('aria-busy');
        button.disabled = false;
        recipient.readOnly = false;
        spinner.hidden = true;
        label.textContent = 'Enviar teste';
        status.textContent = '';
    });
})();
</script>
<?= view('layout/footer') ?>
