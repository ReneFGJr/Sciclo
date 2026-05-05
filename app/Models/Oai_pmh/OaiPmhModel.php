<?php
namespace App\Models\Oai_pmh;

use CodeIgniter\Model;
use SimpleXMLElement;
use Throwable;

class OaiPmhModel extends Model
{
    protected $table = 'oai_pmh';
    protected $primaryKey = 'id';
    protected $allowedFields = ['base_url', 'status', 'base_url_oai', 'repository_name', 'protocol_version', 'admin_email', 'earliest_datestamp', 'deleted_record', 'granularity', 'compression', 'raw_identify_xml', 'created_at', 'updated_at'];
    public $timestamps = false;

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
        return ['status' => '200', 'message' => 'Resposta HTTP ' . $httpCode];
    }

    function getIdentifyOAI($idRepo)
    {
        $data = $this->find($idRepo);
        if ($data['base_url_oai'] == '')
            {
                $baseUrl = trim($data['base_url']);
                if (substr($baseUrl, -1) != '/') {
                    $baseUrl .= '/';
                }

                /***************************************************  */
                $sufix = ["oai", "oai-pmh", "oai/request", "oai-pmh/request", "xmlui/oai/request", "xmlui/oai-pmh/request"];
                foreach ($sufix as $suf) {
                    // Garante que haja uma barra entre baseUrl e o sufixo
                    $testUrl = $baseUrl . ltrim($suf, '/').'?verb=Identify';
                    echo '<h5>' . $suf . ' - ' . $testUrl . '</h5>';
                    echo '<h5>'.$baseUrl.'</h5>';
                    if ($this->validURL($testUrl)) {
                        $dt = $this->validURLOAI($testUrl);
                        echo view('components/message', ['status' => $dt['status'], 'message' => $testUrl . ' - ' . $dt['message']]);
                        if ($dt['status'] == '200') {
                            $baseUrl = $testUrl;
                            break;
                        }
                        //$baseUrl = $testUrl;
                    }
                }

                if ($dt['status'] != '200') {
                    return ['status' => '500', 'message' => 'Não foi possível identificar um endpoint OAI-PMH válido.'];
                }
                $this->update($idRepo, ['base_url_oai' => $baseUrl, 'status' => 1]);
                return ['status' => '200', 'message' => 'Endpoint OAI-PMH identificado: ' . $baseUrl];
            } else {
                return ['status' => '200', 'message' => 'Endpoint OAI-PMH já identificado: ' . $data['base_url_oai']];
            }
    }
}
