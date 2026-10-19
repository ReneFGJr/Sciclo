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
                'repository_oai_valid',
                'SSL',
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
        if (stripos((string) $response, '<!DOCTYPE') !== false) {
            return ['status' => '500', 'message' => 'Resposta XML invalida.'];
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
        try {
            self::identifyFields((string) $response);
        } catch (\RuntimeException $error) {
            return ['status' => '500', 'message' => $error->getMessage()];
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

    public function validateRepositorySsl(int $idRepo, ?\App\Libraries\RepositorySslValidator $validator = null): array
    {
        $repository = $this->find($idRepo);
        if (!$repository) return ['status' => '500', 'message' => 'Repositorio nao encontrado.'];
        $validator ??= new \App\Libraries\RepositorySslValidator();
        $result = $validator->check((string) $repository['base_url']);
        if (!$this->update($idRepo, ['SSL' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR)])) {
            return ['status' => '500', 'message' => 'Falha ao salvar a validacao SSL.'];
        }
        return ['status' => $result['valid'] === true ? '200' : '500', 'message' => $result['message']];
    }

    public function collectMetadataFormats(int $idRepo): array
    {
        $repository = $this->find($idRepo);
        if (!$repository || empty($repository['base_url_oai'])) {
            return ['status' => '500', 'message' => 'Endpoint OAI-PMH nao identificado para consultar os formatos de metadados.'];
        }
        try {
            $url = \App\Libraries\OaiMetadataFormats::requestUrl($repository['base_url_oai']);
            $body = $this->fetchSoftwareMetadata($url);
            if ($body === null) throw new \RuntimeException('Falha ao consultar ListMetadataFormats.');
            $formats = \App\Libraries\OaiMetadataFormats::parse($body);
        } catch (\RuntimeException $error) {
            return ['status' => '500', 'message' => $error->getMessage()];
        }
        $metadataModel = new OaiPmhMetadataModel();
        $this->db->transStart();
        foreach ($formats as $format) {
            $existing = $metadataModel->where('oai_pmh_id', $idRepo)->where('metadata_prefix', $format['metadata_prefix'])->first();
            $format['oai_pmh_id'] = $idRepo;
            $saved = $existing ? $metadataModel->update((int) $existing['id'], $format) : $metadataModel->insert($format) !== false;
            if (!$saved) {
                $this->db->transRollback();
                return ['status' => '500', 'message' => 'Falha ao salvar os formatos de metadados.'];
            }
        }
        $this->db->transComplete();
        if (!$this->db->transStatus()) return ['status' => '500', 'message' => 'Falha ao salvar os formatos de metadados.'];
        return ['status' => '200', 'message' => count($formats) . ' formatos de metadados identificados e salvos: ' . implode(', ', array_column($formats, 'metadata_prefix')) . '.'];
    }

    public function detectRepositorySoftware(int $idRepo): array
    {
        $repository = $this->find($idRepo);
        if (!$repository) return ['status' => '500', 'message' => 'Repositorio nao encontrado.'];
        $detector = \App\Libraries\RepositorySoftwareDetector::class;
        $detected = [];
        if (!empty($repository['raw_identify_xml'])) {
            $previous = libxml_use_internal_errors(true);
            try {
                $xml = simplexml_load_string($repository['raw_identify_xml'], SimpleXMLElement::class, LIBXML_NONET);
                if ($xml !== false) $detected = self::identifySoftware($xml);
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
            }
        }
        $html = $this->fetchSoftwareMetadata((string) $repository['base_url']);
        $pageSoftware = $html !== null ? $detector::html($html) : [];
        if (!$detected || (($pageSoftware['name'] ?? '') === $detected['name'] && empty($detected['version']))) {
            $detected = $pageSoftware ?: $detected;
        }
        $bases = [];
        foreach (['base_url', 'base_url_oai'] as $field) {
            $parts = parse_url((string) ($repository[$field] ?? ''));
            if (!$parts || empty($parts['host']) || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) continue;
            $origin = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
            $path = rtrim($parts['path'] ?? '', '/');
            $path = preg_replace('~/(?:server/)?oai(?:-pmh)?(?:/request)?$|/(?:home|xmlui|jspui)$~i', '', $path);
            $bases[] = $origin . $path;
            $bases[] = $origin;
        }
        $endpoints = ['DSpace' => ['/server/api', '/api'], 'Dataverse' => ['/api/info/version'], 'CKAN' => ['/api/3/action/status_show']];
        $deadline = microtime(true) + 20;
        foreach (array_unique($bases) as $base) {
            foreach ($endpoints as $name => $paths) {
                if ($detected && $detected['name'] !== $name) continue;
                foreach ($paths as $path) {
                    if (microtime(true) >= $deadline) break 3;
                    $body = $this->fetchSoftwareMetadata($base . $path);
                    $result = $body !== null ? $detector::api($name, $body) : [];
                    if ($result) {
                        $detected = $result;
                        break 3;
                    }
                }
            }
        }
        if (!$detected) {
            return ['status' => '200', 'message' => 'Software e versao nao divulgados pelo repositorio. Os dados existentes foram mantidos.'];
        }
        $software = $this->db->table('repository_software')->where('name', $detected['name'])->get()->getRowArray();
        if (!$software) return ['status' => '500', 'message' => 'Software identificado, mas ausente do cadastro: ' . $detected['name']];
        $fields = ['repository_software' => (int) $software['id']];
        $version = $detector::version($detected['version'] ?? null);
        if ($version !== null && strlen($version) <= 10) {
            $fields['repository_software_version'] = $version;
        } elseif ((int) ($repository['repository_software'] ?? 0) !== (int) $software['id']) {
            $fields['repository_software_version'] = null;
        }
        if (!$this->update($idRepo, $fields)) return ['status' => '500', 'message' => 'Falha ao salvar o software do repositorio.'];
        return ['status' => '200', 'message' => 'Software: ' . $detected['name'] . '. Versao: ' . ($version ?? 'nao divulgada') . '.'];
    }

    protected function fetchSoftwareMetadata(string $url): ?string
    {
        if (!in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)) return null;
        $curl = curl_init($url);
        $body = '';
        curl_setopt_array($curl, [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_USERAGENT => 'Sciclo Repository Identification',
            CURLOPT_HTTPHEADER => ['Accept: application/json, text/html;q=0.9'],
            CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use (&$body): int {
                if (strlen($body) + strlen($chunk) > 2097152) return 0;
                $body .= $chunk;
                return strlen($chunk);
            },
        ]);
        $success = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $success !== false && $status >= 200 && $status < 300 ? $body : null;
    }

    public static function identifyFields(string $body): array
    {
        if (trim($body) === '' || stripos($body, '<!DOCTYPE') !== false) throw new \RuntimeException('Resposta Identify invalida.');
        $previous = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($body, SimpleXMLElement::class, LIBXML_NONET);
            if ($xml === false || $xml->getName() !== 'OAI-PMH'
                || $xml->xpath('/*[local-name()="OAI-PMH"]/*[local-name()="error"]')) {
                throw new \RuntimeException('Resposta Identify invalida.');
            }
            $nodes = $xml->xpath('/*[local-name()="OAI-PMH"]/*[local-name()="Identify"]');
            if (count($nodes ?: []) !== 1) throw new \RuntimeException('Dados Identify ausentes.');
            $fields = [];
            foreach (['repositoryName' => 'repository_name', 'protocolVersion' => 'protocol_version',
                'adminEmail' => 'admin_email', 'earliestDatestamp' => 'earliest_datestamp',
                'deletedRecord' => 'deleted_record', 'granularity' => 'granularity', 'compression' => 'compression'] as $tag => $column) {
                $values = [];
                foreach ($nodes[0]->xpath('./*[local-name()="' . $tag . '"]') ?: [] as $value) {
                    if (trim((string) $value) !== '') $values[] = trim((string) $value);
                }
                if (!$values && $tag !== 'compression') throw new \RuntimeException('Campo obrigatorio ausente no Identify: ' . $tag);
                $fields[$column] = $values ? implode("\n", array_unique($values)) : null;
            }
            return $fields;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function saveIdentification(int $idRepo, string $url, array $result): bool
    {
        try {
            $fields = self::identifyFields($result['identify_xml']);
        } catch (\RuntimeException $error) {
            return false;
        }
        $fields += ['base_url_oai' => $url, 'status' => 1, 'raw_identify_xml' => $result['identify_xml'], 'repository_oai_valid' => true];
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
        return $this->update($idRepo, $fields);
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
        if (!$data) return ['status' => '500', 'message' => 'Repositorio nao encontrado.'];
        if (!$this->update($idRepo, ['repository_oai_valid' => false])) {
            return ['status' => '500', 'message' => 'Falha ao atualizar a validacao OAI-PMH.'];
        }
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
                if (!$this->saveIdentification((int) $idRepo, $baseUrl, $dt)) {
                    return ['status' => '500', 'message' => 'Falha ao salvar os dados Identify do repositorio.'];
                }
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
                if (!$this->saveIdentification((int) $idRepo, $url, $result)) {
                    return ['status' => '500', 'message' => 'Falha ao salvar os dados Identify do repositorio.'];
                }
                return ['status' => '200', 'message' => 'Endpoint OAI-PMH já identificado: ' . $data['base_url_oai']];
            }
    }
}
