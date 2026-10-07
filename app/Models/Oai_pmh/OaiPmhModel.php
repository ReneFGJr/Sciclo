<?php
namespace App\Models\Oai_pmh;

use CodeIgniter\Model;
use SimpleXMLElement;
use Throwable;

class OaiPmhModel extends Model
{
    protected $table = 'oai_pmh';
    protected $primaryKey = 'id';
    protected $allowedFields = [
                'base_url',
                'status',
                'submitted_at',
                'submitted_by',
                'repository_type',
                'repository_software',
                'repository_software_version',
                'base_url_oai',
                'repository_name',
                'protocol_version',
                'admin_email',
                'earliest_datestamp',
                'deleted_record',
                'granularity',
                'compression',
                'raw_identify_xml',
                'created_at',
                'updated_at'];
    public $timestamps = false;

    public function registerSubmitter(int $repositoryId, int $userId): void
    {
        if ($repositoryId > 0 && $userId > 0) {
            $this->where('id', $repositoryId)->where('submitted_by', null)
                ->set(['submitted_by' => $userId])->update();
        }
    }

    /**
     * Retorna o total de repositórios avaliados (registros na tabela oai_pmh).
     * @return int
     */
    public function totalRepositoriosAvaliados()
    {
        return $this->countAllResults();
    }

    function validURL($url)
    {
        $url = trim((string) $url);
        $RSP = ['status' => '200', 'message' => 'URL válida.'];

         if ($url === '') {
            $RSP['status'] = '500';
            $RSP['message'] = 'A URL não pode ser vazia.';
            return $RSP;
        }
            if (! filter_var($url, FILTER_VALIDATE_URL)) {
                $RSP['status'] = '500';
                $RSP['message'] = 'A URL fornecida é inválida.';
                return $RSP;
            }
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            curl_close($curl);
            if ($curlError) {
                $RSP['status'] = '500';
                $RSP['message'] = 'Erro ao acessar a URL: ' . $curlError;
                return $RSP;
            }
        return $RSP;
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

    function validURLOAI($url)
    {
        $url = trim((string) $url);
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);
        if ($curlError) {
            return ['status' => '500', 'message' => 'Erro ao acessar a URL: ' . $curlError];
        }
        if ($httpCode >= 400) {
            return ['status' => '500', 'message' => 'Resposta HTTP ' . $httpCode];
        }
        $previous = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string((string) $response, SimpleXMLElement::class, LIBXML_NONET);
            if ($xml === false || $xml->getName() !== 'OAI-PMH'
                || !$xml->xpath('/*[local-name()="OAI-PMH"]/*[local-name()="Identify"]')) {
                return ['status' => '500', 'message' => 'A URL não retornou uma resposta OAI-PMH Identify válida.'];
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        return ['status' => '200', 'message' => 'Resposta HTTP ' . $httpCode,
            'identify_xml' => (string) $response,
            'software' => self::identifySoftware($xml)];
    }

    public static function identifySoftware(SimpleXMLElement $xml): array
    {
        $descriptions = $xml->xpath('/*[local-name()="OAI-PMH"]/*[local-name()="Identify"]/*[local-name()="description"]');
        foreach ($descriptions ?: [] as $description) {
            $text = $description->asXML();
            foreach (['DSpace', 'EPrints', 'Fedora', 'Invenio', 'Dataverse', 'CKAN'] as $name) {
                if (!preg_match('/\b' . preg_quote($name, '/') . '\b/i', $text)) {
                    continue;
                }
                $version = null;
                $softwareNodes = $description->xpath('.//*[local-name()="software"]');
                foreach ($softwareNodes ?: [] as $software) {
                    $names = $software->xpath('./*[local-name()="name" or local-name()="softwareName"]');
                    if ($names && strcasecmp(trim((string) $names[0]), $name) === 0) {
                        $versions = $software->xpath('./*[local-name()="version" or local-name()="softwareVersion"]');
                        $version = $versions ? trim((string) $versions[0]) : null;
                    }
                }
                if (!$version && preg_match('/\b' . preg_quote($name, '/') . '\s*(?:version\s*|v\s*)?([0-9]+(?:\.[0-9]+)*(?:[-+][a-z0-9.]+)?)/i', strip_tags($text), $matches)) {
                    $version = $matches[1];
                }
                return ['name' => $name, 'version' => $version ?: null];
            }
        }
        return [];
    }

    private function saveIdentification(int $idRepo, string $url, array $result): void
    {
        $fields = ['base_url_oai' => $url, 'status' => 1, 'raw_identify_xml' => $result['identify_xml']];
        $software = $result['software'] ?? [];
        if ($software) {
            $record = $this->db->table('repository_software')->where('name', $software['name'])->get()->getRowArray();
            if ($record) {
                $current = $this->find($idRepo);
                $fields['repository_software'] = $record['id'];
                if ($software['version'] !== null && strlen($software['version']) <= 10) {
                    $fields['repository_software_version'] = $software['version'];
                } elseif ((int) ($current['repository_software'] ?? 0) !== (int) $record['id']) {
                    $fields['repository_software_version'] = null;
                }
            }
        }
        $this->update($idRepo, $fields);
    }

    public static function oaiCandidates(string $url): array
    {
        $parts = parse_url(trim($url));
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return [];
        }
        $origin = $parts['scheme'] . '://' . $parts['host']
            . (isset($parts['port']) ? ':' . $parts['port'] : '');
        $path = rtrim($parts['path'] ?? '', '/');
        $bases = [$origin . preg_replace('~/(home|xmlui|jspui)$~i', '', $path), $origin];
        $candidates = [];
        if (preg_match('~/((server/)?oai(-pmh)?(/request)?)$~i', $path)) {
            $candidates[] = $origin . $path . '?verb=Identify';
        }
        foreach (array_unique($bases) as $base) {
            foreach (['server/oai/request', 'oai/request', 'oai', 'oai-pmh', 'oai-pmh/request', 'xmlui/oai/request', 'xmlui/oai-pmh/request', 'jspui/oai/request'] as $suffix) {
                $candidates[] = $base . '/' . $suffix . '?verb=Identify';
            }
        }
        return array_values(array_unique($candidates));
    }

    function getIdentifyOAI($idRepo)
    {
        $data = $this->find($idRepo);
        if ($data['base_url_oai'] == '')
            {
                $baseUrl = '';
                foreach (self::oaiCandidates($data['base_url']) as $testUrl) {
                    echo '<h5>' . esc($testUrl) . '</h5>';
                        $dt = $this->validURLOAI($testUrl);
                        echo view('components/message', ['status' => $dt['status'], 'message' => $testUrl . ' - ' . $dt['message']]);
                        if ($dt['status'] == '200') {
                            $baseUrl = $testUrl;
                            break;
                        }
                }

                if ($baseUrl === '') {
                    return ['status' => '500', 'message' => 'Não foi possível identificar um endpoint OAI-PMH válido.'];
                }
                $this->saveIdentification((int) $idRepo, $baseUrl, $dt);
                return ['status' => '200', 'message' => 'Endpoint OAI-PMH identificado: ' . $baseUrl];
            } else {
                $url = preg_replace('/([?&])verb=[^&]*/i', '$1verb=Identify', $data['base_url_oai']);
                if (!preg_match('/[?&]verb=/i', $url)) {
                    $url .= (str_contains($url, '?') ? '&' : '?') . 'verb=Identify';
                }
                $result = $this->validURLOAI($url);
                if ($result['status'] !== '200') {
                    return $result;
                }
                $this->saveIdentification((int) $idRepo, $url, $result);
                return ['status' => '200', 'message' => 'Endpoint OAI-PMH já identificado: ' . $data['base_url_oai']];
            }
    }
}
