<?php

namespace Modules\API\Tools;

class StringTool 
{
	/**
	 * @param float $latitude
	 * @param float $longitude
	 * @param float $radius
	 * @return array
	 */
	public static function lineBreak(string $originalString, int $maxLineLength = 30 ) : string
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
