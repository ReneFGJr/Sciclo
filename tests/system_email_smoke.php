<?php
// No network traffic: transport is replaced with a recording fake.
require __DIR__ . '/admin_email_test_smoke.php';

class RecordingEmail extends CodeIgniter\Email\Email
{
    public bool $succeed = true;
    public array $recorded = [];

    public function send($autoClear = true)
    {
        $this->recorded = ['body' => $this->body, 'attachments' => $this->attachments, 'subject' => $this->subject];
        if (!$this->succeed) {
            $this->setErrorMessage('535 Authentication failed test-secret-123');
        }
        return $this->succeed;
    }
}

$config = new Config\Email();
$config->protocol = 'smtp';
$config->fromEmail = 'sender@example.org';
$config->SMTPUser = 'smtp-user';
$config->SMTPPass = 'test-secret-123';
$transport = new RecordingEmail($config);
$mailer = new App\Libraries\SystemEmail($config, $transport);
check($mailer->send('recipient@example.org', "Teste\r\nseguro", 'emails/test', [], 'Mensagem alternativa'), 'Simulated send succeeds');
$parts = $transport->recorded['attachments'];
check(count($parts) === 2 && $parts[0]['multipart'] === 'related' && $parts[1]['disposition'] === 'attachment', 'Inline logo and downloadable attachment');
check(str_contains($transport->recorded['body'], esc('cid:' . $parts[0]['cid'], 'attr')), 'Body references actual attached CID');
check(!str_contains($transport->recorded['subject'], "\n"), 'Subject has no newlines');
$transport->succeed = false;
check(!$mailer->send('recipient@example.org', 'Teste', 'emails/test', [], 'Texto'), 'Transport failure returned');
$details = $mailer->getDiagnostic()['details'];
check(str_contains($details, '535 Authentication failed') && !str_contains($details, $config->SMTPPass), 'Safe transport diagnostic');
check(!$mailer->send('bad-address', 'Teste', 'emails/test', [], 'Texto'), 'Invalid recipient rejected');
$transport->succeed = true;
check($mailer->send('recipient@example.org', 'Teste', 'emails/test', [], 'Texto') && $mailer->getDiagnostic() === null, 'Success clears previous diagnostic');
echo "PASS: shared mailer success, failure, CID, attachment and sanitized diagnostics. No emails sent.\n";
