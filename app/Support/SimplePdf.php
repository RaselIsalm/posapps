<?php

namespace App\Support;

class SimplePdf
{
    public static function response(array $lines, string $filename)
    {
        $text = [];
        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                $text[] = '';

                continue;
            }
            $line = function_exists('iconv') ? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $line) : $line;
            foreach (explode("\n", wordwrap($line ?: '', 96, "\n", true)) as $wrapped) {
                $text[] = $wrapped;
            }
        }

        $pages = array_chunk($text, 48);
        if ($pages === []) {
            $pages = [['']];
        }

        $objects = [];
        $pageIds = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        foreach ($pages as $index => $pageLines) {
            $pageId = 4 + ($index * 2);
            $contentId = $pageId + 1;
            $pageIds[] = $pageId.' 0 R';
            $commands = "BT\n/F1 10 Tf\n50 790 Td\n14 TL\n";
            foreach ($pageLines as $line) {
                $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], (string) $line);
                $commands .= '('.$escaped.") Tj\nT*\n";
            }
            $commands .= 'ET';
            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents '.$contentId.' 0 R >>';
            $objects[$contentId] = '<< /Length '.strlen($commands).">>\nstream\n".$commands."\nendstream";
        }
        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $pageIds).'] /Count '.count($pageIds).' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id." 0 obj\n".$object."\nendobj\n";
        }
        $xrefOffset = strlen($pdf);
        $size = max(array_keys($objects)) + 1;
        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";
        for ($id = 1; $id < $size; $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id] ?? 0);
        }
        $pdf .= "trailer\n<< /Size {$size} /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.str_replace('"', '', $filename).'"',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
