<?php
namespace App\Libraries;

class QuestionnaireBranching
{
    public static function skippedIds(array $questions, array $answers): array
    {
        usort($questions, static function (array $a, array $b): int {
            foreach (['criterio', 'nivel1', 'nivel2', 'nivel3'] as $field) {
                $cmp = strnatcmp((string) ($a[$field] ?? ''), (string) ($b[$field] ?? ''));
                if ($cmp !== 0) return $cmp;
            }
            return (int) $a['id'] <=> (int) $b['id'];
        });
        $positions = array_flip(array_column($questions, 'id'));
        $skipped = [];
        foreach ($questions as $index => $question) {
            $id = (int) $question['id'];
            $answer = (string) ($answers[$id] ?? '');
            if (isset($skipped[$id]) || ($question['tipo_resposta'] ?? '') !== 'SN' || !in_array($answer, ['1', '2'], true)) continue;
            $target = (int) ($question['condicional_' . $answer] ?? 0);
            $end = $positions[$target] ?? -1;
            if ($end <= $index || (string) $questions[$end]['criterio'] !== (string) $question['criterio']) continue;
            for ($i = $index + 1; $i < $end; $i++) {
                $next = $questions[$i];
                if ((string) $next['criterio'] === (string) $question['criterio'] && $next['tipo_resposta'] !== 'INFO') {
                    $skipped[(int) $next['id']] = true;
                }
            }
        }
        return array_keys($skipped);
    }
}
