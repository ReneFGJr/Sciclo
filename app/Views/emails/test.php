<?php ob_start(); ?>
                    <p style="margin:0 0 16px;color:#28834a;font-size:12px;font-weight:bold;letter-spacing:2px;text-align:center;">TESTE DE ENVIO</p>
                    <h1 style="margin:0 0 24px;font-size:28px;line-height:1.3;text-align:center;">Uma conexão que funciona.</h1>
                    <p style="font-size:16px;line-height:1.7;">Olá!</p>
                    <p style="font-size:16px;line-height:1.7;">Esta é uma mensagem de teste enviada pela administração do <strong>Sciclo</strong> para verificar o envio de e-mails da plataforma.</p>
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td style="padding:20px;background-color:#eef7f0;border-radius:10px;color:#25663a;font-size:15px;line-height:1.6;">Se você recebeu esta mensagem, o teste de entrega foi concluído. <strong>Nenhuma ação é necessária.</strong></td></tr></table>
                    <p style="margin:24px 0 0;font-size:14px;line-height:1.7;color:#62776c;">Obrigado por ajudar a manter nossa comunicação em dia.<br>Equipe Sciclo</p>
<?php $content = ob_get_clean(); ?>
<?= view('emails/layout', ['content' => $content, 'logoUrl' => $logoUrl, 'title' => 'Sciclo | E-mail de teste', 'footerText' => 'Sciclo · Mensagem automática de teste']) ?>
