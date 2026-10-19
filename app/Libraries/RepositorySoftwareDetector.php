<?php
namespace App\Libraries;

class RepositorySoftwareDetector
{
    public static function version(mixed $value): ?string
    {
        if (!is_scalar($value)) return null;
        return preg_match('/(?:^|\s|v)(\d+(?:\.\d+)*(?:[-+][a-z0-9.]+)?)(?:\s|$)/i', trim((string) $value), $match)
            ? $match[1] : null;
    }

    public static function api(string $software, string $body): array
    {
        $data = json_decode($body, true);
        if (!is_array($data)) return [];
        $value = match ($software) {
            'DSpace' => $data['dspaceVersion'] ?? null,
            'Dataverse' => ($data['status'] ?? '') === 'OK' ? ($data['data']['version'] ?? null) : null,
            'CKAN' => ($data['success'] ?? false) === true ? ($data['result']['ckan_version'] ?? null) : null,
            default => null,
        };
        $version = self::version($value);
        return $version !== null ? ['name' => $software, 'version' => $version] : [];
    }

    public static function html(string $html): array
    {
        if (trim($html) === '') return [];
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new \DOMDocument();
            if (!$document->loadHTML($html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) return [];
            foreach ($document->getElementsByTagName('meta') as $meta) {
                if (strtolower($meta->getAttribute('name')) !== 'generator') continue;
                $result = self::signature($meta->getAttribute('content'));
                if ($result) return $result;
            }
            // Only inspect explicit product attribution, not arbitrary mentions in repository content.
            foreach ($document->getElementsByTagName('a') as $link) {
                $href = $link->getAttribute('href');
                $host = strtolower((string) parse_url($href, PHP_URL_HOST));
                $name = match ($host) {
                    'dspace.org', 'www.dspace.org' => 'DSpace',
                    'dataverse.org', 'www.dataverse.org' => 'Dataverse',
                    'ckan.org', 'www.ckan.org' => 'CKAN',
                    'inveniosoftware.org', 'www.inveniosoftware.org', 'inveniordm.web.cern.ch' => 'Invenio',
                    default => null,
                };
                if ($name && preg_match('/powered\s+by|built\s+with|desenvolvido|DSpace|Dataverse|CKAN|Invenio/i', $link->textContent)) {
                    $result = self::signature($link->textContent);
                    return ['name' => $name, 'version' => ($result['name'] ?? '') === $name ? $result['version'] : null];
                }
            }
            return [];
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private static function signature(string $text): array
    {
        if (!preg_match('/\b(DSpace|Dataverse|CKAN|Invenio(?:RDM)?)\b(?:\s*(?:version|vers[a\x{00e3}]o|v)?\s*[:\/-]?\s*(\d+(?:\.\d+)*(?:[-+][a-z0-9.]+)?))?/iu', $text, $match)) return [];
        $name = match (strtolower($match[1])) {
            'dspace' => 'DSpace', 'dataverse' => 'Dataverse', 'ckan' => 'CKAN', default => 'Invenio',
        };
        return ['name' => $name, 'version' => $match[2] ?? null];
    }
}
