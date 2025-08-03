<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget\Table;

use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Text\Line;
use Crumbls\Tui\Text\Text;

final class TableCell
{
    public function __construct(public Text $content, public Style $style)
    {
    }

    public static function fromString(string $string): self
    {
        return new self(Text::fromLine(Line::fromString($string)), Style::default());
    }

    public static function fromLine(Line $line): self
    {
        return new self(Text::fromLine($line), Style::default());
    }
}
