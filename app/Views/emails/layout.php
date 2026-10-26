<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= esc($title ?? 'Sciclo') ?></title></head>
<body style="margin:0;padding:0;background-color:#f0f5f3;font-family:Arial,Helvetica,sans-serif;color:#263d35;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f0f5f3;">
        <tr><td align="center" style="padding:32px 16px;">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;max-width:600px;background-color:#ffffff;border:1px solid #dce7e1;border-radius:16px;overflow:hidden;">
                <tr><td style="height:8px;background-color:#28834a;font-size:1px;">&nbsp;</td></tr>
                <tr><td align="center" style="padding:36px 24px 28px;text-align:center;">
                    <img src="<?= esc($logoUrl, 'attr') ?>" alt="Sciclo" width="180" style="display:block;width:180px;max-width:100%;height:auto;margin:0 auto;">
                </td></tr>
                <tr><td style="padding:0 32px 32px;">
<?= $content ?>
                </td></tr>
                <tr><td align="center" style="padding:20px 24px;background-color:#f8faf9;border-top:1px solid #e5ece8;color:#72837a;font-size:12px;line-height:1.6;"><?= esc($footerText ?? 'Sciclo · Mensagem automática da plataforma.') ?></td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
