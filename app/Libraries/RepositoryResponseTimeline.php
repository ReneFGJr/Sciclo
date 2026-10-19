<?php
namespace App\Libraries;

class RepositoryResponseTimeline
{
    private static function date(?string $value): ?\DateTimeImmutable
    {
        if (!$value) return null;
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value);
        $errors = \DateTimeImmutable::getLastErrors();
        return $date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0)) ? $date : null;
    }

    public static function duration(?int $seconds): string
    {
        if ($seconds === null) return 'Sem intervalo anterior';
        $parts = [];
        foreach ([86400 => 'd', 3600 => 'h', 60 => 'min', 1 => 's'] as $unit => $label) {
            $count = intdiv($seconds, $unit);
            $seconds %= $unit;
            if ($count > 0) $parts[] = $count . ' ' . $label;
        }
        return $parts ? implode(' ', $parts) : '0 s';
    }

    public static function build(array $questions, array $answers, ?string $submittedAt): array
    {
        $byId = array_column($questions, null, 'id');
        $submitted = self::date($submittedAt);
        $entries = [];
        $missingDates = 0;
        $afterSubmission = 0;
        foreach ($answers as $answer) {
            $question = $byId[(int) $answer['questao_id']] ?? null;
            $value = trim((string) ($answer['resposta'] ?? ''));
            $decoded = json_decode($value, true);
            if (!$question || ($question['tipo_resposta'] ?? '') === 'INFO' || $value === '' || (is_array($decoded) && $decoded === [])) continue;
            $date = self::date($answer['created_at'] ?? null);
            if ($date === null) { $missingDates++; continue; }
            if ($submitted !== null && $date > $submitted) { $afterSubmission++; continue; }
            $level1 = trim((string) ($question['nivel1'] ?? ''));
            $level2 = trim((string) ($question['nivel2'] ?? ''));
            $entries[] = ['question' => $question, 'answer' => $value, 'at' => $date,
                'group' => $level1 . ($level2 !== '' ? '.' . $level2 : '')];
        }
        usort($entries, static fn ($a, $b): int => ($a['at'] <=> $b['at']) ?: ((int) $a['question']['id'] <=> (int) $b['question']['id']));
        $previous = null;
        $groups = [];
        foreach ($entries as &$entry) {
            $entry['seconds'] = $previous !== null ? $entry['at']->getTimestamp() - $previous->getTimestamp() : null;
            $previous = $entry['at'];
            $key = $entry['group'];
            $groups[$key] ??= ['answers' => 0, 'intervals' => 0, 'seconds' => 0];
            $groups[$key]['answers']++;
            if ($entry['seconds'] !== null) {
                $groups[$key]['intervals']++;
                $groups[$key]['seconds'] += $entry['seconds'];
            }
        }
        unset($entry);
        foreach ($groups as &$group) {
            $group['average_seconds'] = $group['intervals'] > 0 ? (int) round($group['seconds'] / $group['intervals']) : null;
        }
        unset($group);
        uksort($groups, static fn ($a, $b): int => strnatcmp((string) $a, (string) $b));
        return ['entries' => $entries, 'groups' => $groups, 'submitted_at' => $submitted,
            'submission_seconds' => $submitted !== null && $previous !== null ? $submitted->getTimestamp() - $previous->getTimestamp() : null,
            'missing_dates' => $missingDates, 'after_submission' => $afterSubmission];
    }
}
