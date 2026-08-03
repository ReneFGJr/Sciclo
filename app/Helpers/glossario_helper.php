<?php

use App\Models\GlossarioModel;

if (! function_exists('glossario_map')) {
    function glossario_map(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $cache = [];
        try {
            $model = new GlossarioModel();
            $items = $model->findAll();
            foreach ($items as $item) {
                $term = trim((string) ($item['termo'] ?? ''));
                $definition = trim((string) ($item['definicao'] ?? ''));
                if ($term !== '') {
                    $normalized = glossario_normalize_term($term);
                    $cache[$normalized] = $definition;
                }
            }
        } catch (Throwable $e) {
            $cache = [];
        }

        return $cache;
    }
}

if (! function_exists('glossario_conteudo')) {
    function glossario_conteudo(?string $texto): string
    {
        $texto = (string) $texto;
        if ($texto === '') {
            return '';
        }

        $map = glossario_map();

        $result = preg_replace_callback('~\[([^\[\]]+)\]~u', static function (array $matches) use ($map): string {
            $term = trim((string) $matches[1]);
            if ($term === '') {
                return esc($matches[0]);
            }

            $key = glossario_normalize_term($term);
            if (! array_key_exists($key, $map)) {
                return esc($matches[0]);
            }

            $definition = trim((string) $map[$key]);
            $label = esc($term);
            $tip = esc('TIP: ' . ($definition !== '' ? $definition : 'Sem definição cadastrada.'));

            return '<span class="glossario-term" data-bs-toggle="tooltip" data-bs-placement="top" title="' . $tip . '">' . $label . '</span>';
        }, esc($texto)) ?? esc($texto);

        return $result;
    }
}

if (! function_exists('glossario_normalize_term')) {
    function glossario_normalize_term(string $term): string
    {
        $term = trim($term);
        $term = trim($term, " \t\n\r\0\x0B[]");
        $term = function_exists('mb_strtolower') ? mb_strtolower($term, 'UTF-8') : strtolower($term);

        return $term;
    }
}
