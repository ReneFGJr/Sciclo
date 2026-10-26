<?php
// Run: php tests/admin_email_test_smoke.php. Does not send mail or access the database.
define('FCPATH', dirname(__DIR__) . '/public/');
define('ENVIRONMENT', 'testing');
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';
CodeIgniter\Boot::bootConsole($paths);

function check($ok, $message) {
    if (!$ok) throw new RuntimeException($message);
}
function page($method = 'GET', $post = []) {
    $request = new CodeIgniter\HTTP\IncomingRequest(config('App'), new CodeIgniter\HTTP\SiteURI(config('App')), null, new CodeIgniter\HTTP\UserAgent());
    Config\Services::injectMock('request', $request);
    service('validation')->reset();
    $request->setMethod($method);
    $request->setGlobal('post', $post);
    $controller = new App\Controllers\Admin\EmailTest();
    $controller->initController($request, service('response'), service('logger'));
    return $controller->index();
}

$html = page();
check(str_contains($html, 'Enviar teste') && str_contains($html, 'srcdoc='), 'Form and preview render');
check(str_contains($html, 'name="csrf_'), 'CSRF field present');
check(str_contains(page('POST', ['email' => '']), 'Informe o e-mail'), 'Empty recipient rejected');
check(str_contains(page('POST', ['email' => "invalid\r\nBcc: victim@example.org"]), 'Informe um e-mail válido'), 'Invalid recipient and header injection rejected');
// Force a missing sender so this failure test cannot reach any transport.
putenv('EMAIL_FROM=invalid');
$_ENV['EMAIL_FROM'] = $_SERVER['EMAIL_FROM'] = 'invalid';
config('Email')->fromEmail = 'invalid';
$failure = page('POST', ['email' => 'test@example.org']);
check(str_contains($failure, 'Não foi possível enviar') && str_contains($failure, 'Configure EMAIL_FROM'), 'Specific configuration failure displayed');
$config = new Config\Email();
$config->SMTPUser = 'smtp-user';
$config->SMTPPass = 'test-secret-123';
$redact = new ReflectionMethod(App\Libraries\SystemEmail::class, 'redactDiagnostic');
$raw = '535 Authentication failed ' . $config->SMTPPass . ' ' . base64_encode($config->SMTPPass)
    . ' ' . base64_encode("\0" . $config->SMTPUser . "\0" . $config->SMTPPass);
$safe = $redact->invoke(new App\Libraries\SystemEmail(), $raw, $config);
check(str_contains($safe, '535 Authentication failed') && !str_contains($safe, $config->SMTPPass)
    && !str_contains($safe, base64_encode($config->SMTPPass))
    && !str_contains($safe, base64_encode("\0" . $config->SMTPUser . "\0" . $config->SMTPPass)), 'Transport reason preserved and credentials redacted');
$email = new CodeIgniter\Email\Email();
$logo = FCPATH . 'assets/logo/logo_sciclo.png';
$email->attach($logo, 'inline');
$cid = $email->setAttachmentCID($logo);
check(is_string($cid) && $cid !== '', 'Project logo attaches inline');
$body = view('emails/test', ['logoUrl' => 'cid:' . $cid]);
check(str_contains($body, 'src="' . esc('cid:' . $cid, 'attr') . '"') && str_contains($body, 'align="center"'), 'Template embeds centered logo');
echo "PASS: form, preview, recipient validation, configuration failure and inline logo. No emails sent.\n";
