<?php
namespace App\Models\Oai_pmh;

use CodeIgniter\Model;
use SimpleXMLElement;
use Throwable;

class OaiPmhModel extends Model
{
    protected $table = 'oai_pmh';
    protected $primaryKey = 'id';
    protected $allowedFields = ['base_url', 'repository_name', 'protocol_version', 'admin_email', 'earliest_datestamp', 'deleted_record', 'granularity', 'compression', 'raw_identify_xml', 'created_at', 'updated_at'];
    public $timestamps = false;

    function validURL($url)
    {
        $url = trim((string) $url);
        return $url !== '' && filter_var($url, FILTER_VALIDATE_URL);
    }

    /**********************************************************************************************************  */
    function saveURL($url)
    {
        $url = trim((string) $url);

        if (! $this->validURL($url)) {
            $complement = ['/oai','/oai-pmh'];
            foreach ($complement as $comp) {
                $testUrl = $url . $comp;
                echo '<h5>'.$testUrl.'</h5>';
                if ($this->validURL($testUrl)) {
                    $url = $testUrl;
                    break;
                }
            }

             if (! $this->validURL($url)) {
                return null;
            }
        }

        if ($url === '') {
            return null;
        }

        $existing = $this->where('base_url', $url)->first();

        if (! empty($existing)) {
            return (int) $existing['id'];
        }

        $data = [
            'base_url' => $url,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->insert($data);
        return $this->getInsertID();
    }

    function getIdentify($baseUrl)
    {
        $baseUrl = trim((string) $baseUrl);

        if ($baseUrl === '' || ! filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            return ' URL invalida.';
        }

        try {
            $client = service('curlrequest', [
                'timeout' => 30,
                'http_errors' => false,
            ]);
            $sslFallbackUsed = false;

            try {
                $response = $client->request('GET', $baseUrl, [
                    'query' => ['verb' => 'Identify'],
                ]);
            } catch (Throwable $requestError) {
                $requestErrorMessage = $requestError->getMessage();
                $isSslCertError = str_contains($requestErrorMessage, 'SSL certificate problem')
                    || str_contains($requestErrorMessage, 'cURL error 60');

                if (! $isSslCertError) {
                    throw $requestError;
                }

                // Fallback para ambiente local com cadeia de certificado incompleta.
                $response = $client->request('GET', $baseUrl, [
                    'query' => ['verb' => 'Identify'],
                    'verify' => false,
                ]);
                $sslFallbackUsed = true;
            }

            $statusCode = $response->getStatusCode();
            $xmlBody = (string) $response->getBody();

            if ($statusCode >= 400) {
                return ' falha HTTP ' . $statusCode . ' no Identify.';
            }

            if ($xmlBody === '') {
                return ' resposta vazia no Identify.';
            }

            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($xmlBody);

            if (! ($xml instanceof SimpleXMLElement)) {
                return ' XML invalido no Identify.';
            }

            $identifyNodes = $xml->xpath('//*[local-name()="Identify"]');
            if (empty($identifyNodes) || ! ($identifyNodes[0] instanceof SimpleXMLElement)) {
                return ' elemento Identify nao encontrado.';
            }

            $identify = $identifyNodes[0];

            $adminEmails = $identify->xpath('./*[local-name()="adminEmail"]');
            $compressions = $identify->xpath('./*[local-name()="compression"]');

            $adminEmailValues = [];
            if (is_array($adminEmails)) {
                foreach ($adminEmails as $emailNode) {
                    $value = trim((string) $emailNode);
                    if ($value !== '') {
                        $adminEmailValues[] = $value;
                    }
                }
            }

            $compressionValues = [];
            if (is_array($compressions)) {
                foreach ($compressions as $compressionNode) {
                    $value = trim((string) $compressionNode);
                    if ($value !== '') {
                        $compressionValues[] = $value;
                    }
                }
            }

            $repositoryNameNodes = $identify->xpath('./*[local-name()="repositoryName"]');
            $protocolVersionNodes = $identify->xpath('./*[local-name()="protocolVersion"]');
            $earliestDatestampNodes = $identify->xpath('./*[local-name()="earliestDatestamp"]');
            $deletedRecordNodes = $identify->xpath('./*[local-name()="deletedRecord"]');
            $granularityNodes = $identify->xpath('./*[local-name()="granularity"]');

            $existing = $this->where('base_url', $baseUrl)->first();

            if (empty($existing)) {
                $this->insert([
                    'base_url' => $baseUrl,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $recordId = (int) $this->getInsertID();
            } else {
                $recordId = (int) $existing['id'];
            }

            $data = [
                'repository_name' => isset($repositoryNameNodes[0]) ? trim((string) $repositoryNameNodes[0]) : null,
                'protocol_version' => isset($protocolVersionNodes[0]) ? trim((string) $protocolVersionNodes[0]) : null,
                'admin_email' => empty($adminEmailValues) ? null : implode(';', $adminEmailValues),
                'earliest_datestamp' => isset($earliestDatestampNodes[0]) ? trim((string) $earliestDatestampNodes[0]) : null,
                'deleted_record' => isset($deletedRecordNodes[0]) ? trim((string) $deletedRecordNodes[0]) : null,
                'granularity' => isset($granularityNodes[0]) ? trim((string) $granularityNodes[0]) : null,
                'compression' => empty($compressionValues) ? null : implode(';', $compressionValues),
                'raw_identify_xml' => $xmlBody,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $this->update($recordId, $data);

            if ($sslFallbackUsed) {
                return ' Identify salvo com sucesso (SSL sem validacao, ajuste o CA no servidor).';
            }

            return ' Identify salvo com sucesso.';
        } catch (Throwable $e) {
            return ' erro no Identify: ' . $e->getMessage();
        }
    }
}
