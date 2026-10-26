<?php ob_start(); ?>
<p style="margin:0 0 16px;color:#8b5907;font-size:12px;font-weight:bold;letter-spacing:2px;">ACOMPANHAMENTO DA SUBMISSÃO</p>
<h1 style="margin:0 0 18px;color:#153c54;font-size:28px;line-height:1.25;">Vamos completar sua submissão?</h1>
<p style="font-size:16px;line-height:1.7;">Olá! A equipe Sciclo revisou os dados de <strong><?= esc($repository['repository_name'] ?: 'seu repositório') ?></strong> e identificou informações que precisam ser complementadas antes de prosseguir com a avaliação.</p>
<p style="display:inline-block;background:#fff3d6;color:#80580f;padding:9px 14px;border-radius:20px;font-size:13px;font-weight:bold;">Revisões necessárias</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td style="padding:22px;background:#f4f8fa;border-left:4px solid #35b5a5;">
<h2 style="font-size:17px;margin:0 0 12px;color:#153c54;">O que precisa ser ajustado</h2>
<div style="font-size:15px;line-height:1.8;word-break:break-word;"><?= nl2br(esc($movement['details'])) ?></div>
</td></tr></table>
<p style="font-size:15px;line-height:1.8;">Suas respostas e evidências foram preservadas. Entre com a conta responsável pela submissão, faça os ajustes indicados e confirme o reenvio para avaliação.</p>
<table role="presentation" cellspacing="0" cellpadding="0" style="margin:26px 0;"><tr><td bgcolor="#126b72" style="border-radius:8px;"><a href="<?= esc($url, 'attr') ?>" style="display:inline-block;padding:16px 24px;color:#fff;text-decoration:none;font-size:16px;font-weight:bold;">Completar dados do repositório →</a></td></tr></table>
<p style="font-size:12px;color:#657987;line-height:1.7;">Se o botão não abrir, copie este endereço para o navegador:<br><a href="<?= esc($url, 'attr') ?>" style="color:#126b72;word-break:break-all;"><?= esc($url) ?></a></p>
<p style="margin:28px 0 0;font-size:15px;line-height:1.6;">Atenciosamente,<br><strong>Equipe Sciclo</strong></p>

<?php $content = ob_get_clean(); ?>
<?= view('emails/layout', ['content' => $content, 'logoUrl' => $logoUrl ?? 'cid:' . $cid, 'title' => 'Revisões necessárias | Sciclo', 'footerText' => 'Sciclo · Acompanhamento da submissão #' . (int) $repository['id']]) ?>
