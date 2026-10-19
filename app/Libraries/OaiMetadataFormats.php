<?php
namespace App\Libraries;

use RuntimeException;

class OaiMetadataFormats
{
    public static function requestUrl(string $endpoint): string
    {
        $parts = parse_url($endpoint);
        if (!$parts || empty($parts['host']) || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
            throw new RuntimeException('Endpoint OAI-PMH invalido.');
        }
        parse_str($parts['query'] ?? '', $query);
        foreach (['verb', 'identifier', 'metadataPrefix', 'resumptionToken', 'from', 'until', 'set'] as $argument) unset($query[$argument]);
        $query['verb'] = 'ListMetadataFormats';
        return explode('?', explode('#', $endpoint, 2)[0], 2)[0] . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    public static function parse(string $body): array
    {
        if (trim($body) === '' || stripos($body, '<!DOCTYPE') !== false) throw new RuntimeException('Resposta XML invalida.');
        $previous = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($body, \SimpleXMLElement::class, LIBXML_NONET);
            if ($xml === false || $xml->getName() !== 'OAI-PMH') throw new RuntimeException('Resposta OAI-PMH invalida.');
            $xml->registerXPathNamespace('oai', 'http://www.openarchives.org/OAI/2.0/');
            $errors = $xml->xpath('/oai:OAI-PMH/oai:error');
            if ($errors) throw new RuntimeException('OAI-PMH: ' . (string) $errors[0]['code'] . ' - ' . trim((string) $errors[0]));
            $formats = [];
            foreach ($xml->xpath('/oai:OAI-PMH/oai:ListMetadataFormats/oai:metadataFormat') ?: [] as $format) {
                $fields = $format->children('http://www.openarchives.org/OAI/2.0/');
                $prefix = trim((string) $fields->metadataPrefix);
                $schema = trim((string) $fields->schema);
                $namespace = trim((string) $fields->metadataNamespace);
                if ($prefix === '' || strlen($prefix) > 255 || $schema === '' || $namespace === '') {
                    throw new RuntimeException('Formato de metadados incompleto.');
                }
                $formats[$prefix] = ['metadata_prefix' => $prefix, 'schema_url' => $schema, 'metadata_namespace' => $namespace];
            }
            if (!$formats) throw new RuntimeException('Nenhum formato de metadados foi anunciado pelo OAI-PMH.');
            return array_values($formats);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
