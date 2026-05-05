<?php

/**
 * CodeIgniter Form Helpers
 *
 * @package     CodeIgniter
 * @subpackage  Forms SisDoc
 * @category    Helpers
 * @author      Rene F. Gabriel Junior <renefgj@gmail.com>
 * @link        http://www.sisdoc.com.br/CodIgniter
 * @version     v0.24.04.16
 */

 function searchGoogle($isbn = '')
    {
        $url = 'https://www.google.com/search?num=10&newwindow=1&q=ISBN+'.$isbn.'&sa=X';
        $btn = '<a href="'.$url.'" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-google"></i> Google</a>';
        return $btn;
    }


# [cover_image]
function cover_image($isbn)
{
    if ($isbn == '') {
        return '<img src="' . base_url('assets/img/no_cover.png') . '" alt="Sem capa" class="img-fluid">';
    }
    $path = FCPATH . '_covers/image/' . $isbn . '.jpg';

    if (file_exists($path)) {
        return base_url('/_covers/image/' . $isbn . '.jpg');
    } else {
        $urls = [
            'https://www.ufrgs.br/find/_covers/image/'.$isbn.'.jpg',
            'https://covers.openlibrary.org/b/isbn/' . $isbn . '-L.jpg'
            //env('covers.apiURL', 'https://covers.openlibrary.org/b/isbn/') . $isbn . '-M.jpg',
            //env('covers.apiURL', 'https://covers.openlibrary.org/b/isbn/') . $isbn . '-S.jpg',
        ];
        if (!download_cover($isbn,$urls))
         {
            return base_url('assets/img/no_cover.png');
         } else {
            return base_url('/_covers/image/' . $isbn . '.jpg');
         }
        //env('covers.apiURL', 'https://covers.openlibrary.org/b/isbn/') . $isbn . '-L.jpg');
        return base_url('assets/img/no_cover.png');
    }

    $base = env('covers.baseURL', base_url('/_covers/image/'));
    return $base . $isbn . '.jpg';
}

function download_cover($isbn, $urls)
{
    $path = FCPATH . '_covers/image/' . $isbn . '.jpg';
    /* Checar diretorio */
    check_directory(FCPATH . '_covers/image/');
    if (!file_exists($path)) {
        foreach ($urls as $url) {
            //echo "Tentando baixar capa de: $url<br>\n";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $data = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            //echo "Status: $http_code<br>\n";
            if ($http_code == 200 && $data) {
                /* Checar se GIF (capa inválida) */
                // Verifica assinatura GIF nos primeiros bytes
                $isGif = (substr($data, 0, 6) === "GIF87a" || substr($data, 0, 6) === "GIF89a");
                if ($isGif) {
                    //echo "Capa inválida (GIF encontrado).<br>\n";
                    continue;
                }
                file_put_contents($path, $data);
                curl_close($ch);
                return true;
            }
            curl_close($ch);
        }
        $fileO = FCPATH . 'public/assets/img/no_cover.png';
        if (file_exists($fileO)) {
            file_put_contents($path, file_get_contents($fileO));
        } else {
            // Se a imagem de fallback não existir, cria um arquivo vazio para evitar tentativas futuras
            $fileO = FCPATH . 'assets/img/no_cover.png';
            if (file_exists($fileO)) {
                file_put_contents($path, file_get_contents($fileO));
            } else {
                echo "OPS, arquivo de fallback para capa não encontrado: " . $fileO;
                exit;
            }

        }
        return false;
    } else {
        return true;
    }
}

/**
 * Verifica se o diretório existe, cria se não existir e adiciona .htaccess restritivo
 * @param string $dir Caminho absoluto do diretório
 */
function check_directory($dir)
{
    // Garante barra no final do diretório
    $dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    // Cria diretório se não existir
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            throw new Exception("Erro ao criar diretório: " . $dir);
        }
    }

    // Caminho do .htaccess
    $htaccess = $dir . '.htaccess';

    // Cria .htaccess apenas se não existir
    if (!file_exists($htaccess)) {

        $conteudo = <<<HTACCESS
# Bloqueia execução de scripts perigosos
<FilesMatch "\.(php|php5|php7|php8|phtml|phar|pl|py|jsp|asp|aspx|cgi|sh)$">
    Require all denied
</FilesMatch>

# Permite imagens
<FilesMatch "\.(jpg|jpeg|png|gif|webp|svg)$">
    Require all granted
</FilesMatch>

# Evita listagem de diretório
Options -Indexes
HTACCESS;

        if (file_put_contents($htaccess, $conteudo) === false) {
            throw new Exception("Erro ao criar .htaccess em: " . $htaccess);
        }
    }

    return true;
}

function get($var)
    {
        $RSP = '';
        if (isset($_POST[$var]))
            {
                $RSP = $_POST[$var];
            }
        if (isset($_GET[$var])) {
            $RSP = $_GET[$var];
        }
        return $RSP;
    }

function pre($dt, $force = true)
{
    echo '<pre>';
    print_r($dt);
    echo '</pre>';
    if ($force) {
        exit;
    }
}

function sonumero($n)
    {
        $n = preg_replace('/[^0-9]/', '', $n);
        return $n;
    }

function ascii($d)
{    //$d = strtoupper($d);

    $gr = array();
    $gr['-'] = '–';
    $gr['.'] = 'º';
    $gr['A'] = 'Â;À;Á;Ä;Ã;Å';
    $gr['a'] = 'â;ã;à;á;ä';
    $gr['E'] = 'Ê;È;É;Ë';
    $gr['e'] = 'ê;è;é;ë';
    $gr['I'] = 'Î;Í;Ì;Ï';
    $gr['i'] = 'î;í;ì;ï';
    $gr['O'] = 'Ô;Õ;Ò;Ó;Ö';
    $gr['o'] = 'ô;õ;ò;ó;ö';
    $gr['U'] = 'Û;Ù;Ú;Ü';
    $gr['u'] = 'û;ú;ù;ü';
    $gr['c'] = 'ç';
    $gr['C'] = 'Ç';

    foreach ($gr as $lt => $gpa) {
        $grp = explode(';', $gpa);
        for ($r = 0; $r < count($grp); $r++) {
            $d = troca($d, $grp[$r], $lt);
        }
    }
    $name = '';
    for ($r = 0; $r < strlen($d); $r++) {
        $c = substr($d, $r, 1);
        $o = ord($c);
        if ($o <= 127) {
            $name .= $c;
        }
    }
    return $name;
}

/* Funcao troca */
function troca($qutf, $qc, $qt)
{
    if (!is_array($qc)) {
        $qc = array($qc);
    }
    if (!is_array($qt)) {
        $qt = array($qt);
    }
    return (str_replace($qc, $qt, $qutf));
}