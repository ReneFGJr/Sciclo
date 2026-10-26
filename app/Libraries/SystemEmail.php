<?php

namespace App\Libraries;

/** Shared system mailer. Views receive logoUrl and cid for the embedded project logo. */
class SystemEmail
{
    private ?array $diagnostic = null;

    public function __construct(private ?\Config\Email $config = null, private ?\CodeIgniter\Email\Email $transport = null)
    {
    }

    public function send(string $recipient, string $subject, string $view, array $data, string $plainText): bool
    {
        $this->diagnostic = null;
        $email = null;
        $config = null;
        try {
            $config = clone ($this->config ?? config('Email'));
            if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('O destinatário é inválido.');
            }
            if (!filter_var($config->fromEmail, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('O remetente está vazio ou é inválido. Configure EMAIL_FROM ou email.fromEmail com um endereço de e-mail válido.');
            }
            if ($config->protocol === 'smtp' && ($config->SMTPUser === '' || $config->SMTPPass === '')) {
                throw new \RuntimeException('Preencha EMAIL_USER_AUTH e EMAIL_PASSWORD no .env para autenticar no servidor SMTP.');
            }
            $email = $this->transport ?? \Config\Services::email($config, false);
            $email->clear(true);
            $email->setFrom($config->fromEmail, $config->fromName ?: 'Sciclo');
            $email->setTo($recipient);
            $email->setSubject('Sciclo | ' . preg_replace('/[\r\n]+/', ' ', $subject));
            $email->setMailType('html');
            $logo = FCPATH . 'assets/logo/logo_sciclo.png';
            $email->attach($logo, 'inline');
            $cid = $email->setAttachmentCID($logo);
            if (!$cid) {
                throw new \RuntimeException('Logo indisponível.');
            }
            // Keep the embedded CID image and also offer a downloadable attachment.
            if ($email->attach($logo, 'attachment', 'logo-sciclo.png') === false) {
                throw new \RuntimeException('Não foi possível anexar o logo Sciclo.');
            }
            $email->setMessage(view($view, array_merge($data, ['logoUrl' => 'cid:' . $cid, 'cid' => $cid])));
            $email->setAltMessage($plainText);
            if (!$email->send()) {
                throw new \RuntimeException('O serviço de e-mail não confirmou o envio. Consulte o retorno do servidor abaixo.');
            }
            return true;
        } catch (\Throwable $error) {
            $details = $error->getMessage();
            if ($email !== null) {
                // Exclude message headers and body from the transport diagnostic.
                $debug = $email->printDebugger([]);
                $details .= "\n\n" . html_entity_decode(strip_tags(str_replace(
                    ['</pre>', '<br>', '<br />', '<br/>'], "\n", $debug
                )), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
            $details = $this->redactDiagnostic($details, $config);
            $this->diagnostic = [
                'details' => $details,
                'protocol' => $config->protocol ?? 'não definido',
                'host' => $this->redactDiagnostic((string) ($config->SMTPHost ?? ''), $config),
                'port' => $config->SMTPPort ?? '',
                'crypto' => $config->SMTPCrypto ?? '',
                'from' => $config->fromEmail ?? '',
            ];
            log_message('error', 'Falha no envio de e-mail Sciclo: {details}', ['details' => $details]);
            return false;
        }
    }

    public function getDiagnostic(): ?array
    {
        return $this->diagnostic;
    }

    public function redactDiagnostic(string $text, ?\Config\Email $config): string
    {
        $secrets = [];
        if ($config !== null) {
            foreach ([$config->SMTPPass, $config->SMTPUser] as $secret) {
                if ($secret !== '') {
                    $secrets[] = $secret;
                    $secrets[] = base64_encode($secret);
                    $secrets[] = rawurlencode($secret);
                }
            }
            if ($config->SMTPPass !== '') {
                $secrets[] = base64_encode("\0" . $config->SMTPUser . "\0" . $config->SMTPPass);
            }
        }
        usort($secrets, static fn ($a, $b) => strlen($b) <=> strlen($a));
        $text = str_replace($secrets, '[oculto]', $text);
        return trim((string) preg_replace('~(://)[^\s/@]+:[^\s/@]*@~', '$1[oculto]@', $text));
    }
}
