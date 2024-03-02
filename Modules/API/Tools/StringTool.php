<?php

namespace Modules\API\Tools;

class StringTool
{
    /**
     * @param string $originalString
     * @param int $maxLineLength
     * @return string
     */
    public static function lineBreak(string $originalString, int $maxLineLength = 30): string
    {
        $output = '';

        $words = explode(" ", $originalString);

        $lines = array();
        $currentLine = '';

        foreach ($words as $word) {
            $potentialLine = $currentLine . ' ' . $word;
            if (strlen($potentialLine) <= $maxLineLength) {
                $currentLine = ltrim($potentialLine);
            } else {
                $lines[] = $currentLine;
                $currentLine = $word;
            }
        }

        if (!empty($currentLine)) {
            $lines[] = $currentLine;
        }

        foreach ($lines as $line) {
            $output .= $line . "<br>";
        }

        return $output;
    }
}
