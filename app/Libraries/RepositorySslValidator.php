<?php
namespace App\Libraries;

class RepositorySslValidator
{
    public function check(string $url): array
    {
        $result = ['status' => 'error', 'valid' => null, 'repository_url' => $url, 'checked_url' => null,
            'checked_at' => gmdate('c'), 'message' => 'URL do repositorio invalida.'];
        $parts = parse_url($url);
        if (!$parts || empty($parts['host']) || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) return $result;
        // HTTP submissions are probed on HTTPS so that their certificate can also be assessed.
        $port = $parts['port'] ?? 443;
        if (strtolower($parts['scheme']) === 'http' && $port === 80) $port = 443;
        $checkedUrl = 'https://' . $parts['host'] . ($port !== 443 ? ':' . $port : '') . ($parts['path'] ?? '/');
        $result['checked_url'] = $checkedUrl;
        $probe = $this->probe($checkedUrl);
        $result['curl_errno'] = $probe['errno'];
        $result['verification_code'] = $probe['verify_result'];
        $result['http_status'] = $probe['http_status'];
        $result['certificate'] = $probe['certificate'] ?? null;
        if ($probe['errno'] === 0 && $probe['verify_result'] === 0) {
            $result['status'] = 'valid';
            $result['valid'] = true;
            $result['message'] = 'Certificado SSL valido: cadeia de confianca, validade e dominio verificados.';
        } elseif ($probe['errno'] === CURLE_SSL_CACERT || ($probe['errno'] !== CURLE_SSL_CACERT_BADFILE && $probe['verify_result'] > 0)) {
            $result['status'] = 'invalid';
            $result['valid'] = false;
            $result['message'] = 'Certificado SSL reprovado na validacao: ' . $probe['error'];
        } else {
            $result['message'] = 'Nao foi possivel concluir a validacao SSL: ' . $probe['error'];
        }
        return $result;
    }

    protected function probe(string $url): array
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_NOBODY => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CERTINFO => true,
            CURLOPT_USERAGENT => 'Sciclo SSL Validation',
        ]);
        curl_exec($curl);
        $result = ['errno' => curl_errno($curl), 'error' => curl_error($curl),
            'verify_result' => (int) curl_getinfo($curl, CURLINFO_SSL_VERIFYRESULT),
            'http_status' => (int) curl_getinfo($curl, CURLINFO_HTTP_CODE), 'certificate' => null];
        $chain = curl_getinfo($curl, CURLINFO_CERTINFO);
        $pem = $chain[0]['Cert'] ?? null;
        if (is_string($pem)) {
            $certificate = openssl_x509_parse($pem);
            if ($certificate !== false) {
                $result['certificate'] = [
                    'subject' => $certificate['subject'] ?? [],
                    'issuer' => $certificate['issuer'] ?? [],
                    'valid_from' => isset($certificate['validFrom_time_t']) ? gmdate('c', $certificate['validFrom_time_t']) : null,
                    'valid_until' => isset($certificate['validTo_time_t']) ? gmdate('c', $certificate['validTo_time_t']) : null,
                    'subject_alt_name' => $certificate['extensions']['subjectAltName'] ?? null,
                ];
            }
        }
        curl_close($curl);
        return $result;
    }
}
