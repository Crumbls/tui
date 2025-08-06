<?php

namespace Crumbls\Tui\Components;

class Paragraph extends Component
{
	protected string $contents = '';

	public static function make(string $contents = ''): self
	{
		$paragraph = new self();
		$paragraph->contents = $contents;
		return $paragraph;
	}
	
	public function content(string $contents): self
	{
		$this->contents = $contents;
		return $this;
	}

	public function render() : string {
		// Render the content, wrapping to fit width if needed
		$width = $this->getWidth();
		$lines = explode("\n", $this->contents);
		$wrappedLines = [];
		
		foreach ($lines as $line) {
			if (mb_strlen($line) <= $width) {
				$wrappedLines[] = $line;
			} else {
				// Simple word wrapping
				$words = explode(' ', $line);
				$currentLine = '';
				
				foreach ($words as $word) {
					if (mb_strlen($currentLine . ' ' . $word) <= $width) {
						$currentLine .= ($currentLine ? ' ' : '') . $word;
					} else {
						if ($currentLine) {
							$wrappedLines[] = $currentLine;
						}
						$currentLine = $word;
					}
				}
				if ($currentLine) {
					$wrappedLines[] = $currentLine;
				}
			}
		}
		
		return implode("\n", $wrappedLines);
	}
}