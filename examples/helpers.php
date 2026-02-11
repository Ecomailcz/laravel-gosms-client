<?php

declare(strict_types = 1);

/**
 * @param list<string> $headers
 * @param array<int, array<string, mixed>> $rows
 * @return array<string, int>
 */
function calculateColumnWidths(array $headers, array $rows): array
{
    $widths = [];

    foreach ($headers as $header) {
        $widths[$header] = mb_strlen($header);
    }

    foreach ($rows as $row) {
        foreach ($headers as $header) {
            $value = formatCellValue($row[$header] ?? '');
            $widths[$header] = max($widths[$header], mb_strlen($value));
        }
    }

    return $widths;
}

/**
 * @param array<string, int> $widths
 */
function tableSeparator(array $widths): string
{
    return '+' . implode('+', array_map(
        static fn (int $width): string => str_repeat('-', $width + 2),
        $widths,
    )) . '+';
}

/**
 * @param list<string> $headers
 * @param array<string, mixed> $row
 * @param array<string, int> $widths
 */
function tableRow(array $headers, array $row, array $widths): string
{
    return '|' . implode('|', array_map(
        static fn (string $header): string => ' ' . str_pad(formatCellValue($row[$header] ?? ''), $widths[$header]) . ' ',
        $headers,
    )) . '|';
}

/**
 * @param array<int, array<string, mixed>> $rows
 */
function printTable(array $rows): void
{
    if ($rows === []) {
        echo "  (no data)\n";

        return;
    }

    $headers = array_keys($rows[0]);
    $widths = calculateColumnWidths($headers, $rows);
    $separator = tableSeparator($widths);

    echo $separator . "\n";
    echo tableRow($headers, array_combine($headers, $headers), $widths) . "\n";
    echo $separator . "\n";

    foreach ($rows as $row) {
        echo tableRow($headers, $row, $widths) . "\n";
    }

    echo $separator . "\n";
}

function formatCellValue(mixed $value): string
{
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if (is_scalar($value)) {
        return (string) $value;
    }

    $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    return $encoded !== false ? $encoded : '';
}
