# E-mails do sistema

Use `App\Libraries\SystemEmail` para os envios da plataforma. A configuração é lida de `Config\Email`, que carrega `EMAIL_TYPE`, `EMAIL_SMTP`, `EMAIL_SMTP_PORT`, `EMAIL_FROM`, `EMAIL_USER_AUTH` e `EMAIL_PASSWORD` do `.env`. Para SMTP na porta 465, a conexão usa TLS implícito.

```php
$mailer = new \App\Libraries\SystemEmail();
$sent = $mailer->send(
    'destinatario@exemplo.com',
    'Assunto da mensagem',
    'emails/minha_mensagem',
    ['nome' => $nome],
    'Versão da mensagem em texto simples.'
);
if (!$sent) {
    $diagnostic = $mailer->getDiagnostic();
}
```

O assunto recebe o prefixo `Sciclo | `. A biblioteca valida destinatário e remetente, envia HTML e texto simples, incorpora o logo via CID e inclui `logo-sciclo.png` como anexo para download. Falhas retornam `false`, registram diagnóstico no log e disponibilizam detalhes com credenciais ocultas. Exiba esses detalhes somente na área administrativa.

As views recebem `logoUrl` e `cid` automaticamente. Para manter o padrão visual, renderize o conteúdo dentro de `emails/layout`, como em `emails/test` e `emails/repository_revision`. Escape dados variáveis com `esc()` antes de incluí-los no HTML. O campo `content` do layout recebe HTML já renderizado.
