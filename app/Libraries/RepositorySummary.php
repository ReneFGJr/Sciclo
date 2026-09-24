<?php
namespace App\Libraries;

class RepositorySummary
{
    public static function build(array $criteriaGroups, array $answers, array $evidencesByQuestion): array
    {
        $empty = ['total' => 0, 'answered' => 0, 'pending' => 0, 'yes' => 0, 'no' => 0,
            'other' => 0, 'comments' => 0, 'evidences' => 0, 'withEvidence' => 0];
        $summary = $empty;
        $groups = [];
        foreach ($criteriaGroups as $level => $questions) {
            $group = $empty;
            foreach ($questions as $question) {
                if (($question['tipo_resposta'] ?? '') === 'INFO') {
                    continue;
                }
                $group['total']++;
                $id = (int) $question['id'];
                $answer = $answers[$id] ?? [];
                $value = trim((string) ($answer['resposta'] ?? ''));
                $decoded = json_decode($value, true);
                $hasAnswer = $value !== '' && !(is_array($decoded) && $decoded === []);
                if ($hasAnswer) {
                    $group['answered']++;
                    $normalized = mb_strtolower($value, 'UTF-8');
                    if (($question['tipo_resposta'] ?? '') === 'SN' && in_array($normalized, ['1', 'sim'], true)) {
                        $group['yes']++;
                    } elseif (($question['tipo_resposta'] ?? '') === 'SN' && in_array($normalized, ['2', 'não', 'nao'], true)) {
                        $group['no']++;
                    } else {
                        $group['other']++;
                    }
                } else {
                    $group['pending']++;
                }
                $group['comments'] += trim((string) ($answer['comentario'] ?? '')) !== '' ? 1 : 0;
                $count = count($evidencesByQuestion[$id] ?? []);
                $group['evidences'] += $count;
                $group['withEvidence'] += $count > 0 ? 1 : 0;
            }
            foreach ($empty as $key => $_) {
                $summary[$key] += $group[$key];
            }
            $group['completion'] = $group['total'] > 0 ? round(100 * $group['answered'] / $group['total'], 1) : 0;
            $groups[$level] = $group;
        }
        $summary['completion'] = $summary['total'] > 0 ? round(100 * $summary['answered'] / $summary['total'], 1) : 0;
        $summary['groups'] = $groups;
        return $summary;
    }
}
