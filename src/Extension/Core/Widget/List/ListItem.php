<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget\List;

use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Text\Text;

final class ListItem
{
    public function __construct(
        public Text $content,
        public Style $style
    ) {
    }

    public static function new(Text $text): self
    {
        return new self($text, Style::default());
    }

    public function style(Style $style): self
    {
        $this->style = $style;

        return $this;
    }

    /**
     * @return int<0,max>
     */
    public function height(): int
    {
        return $this->content->height();
    }

    public static function fromString(string $string): self
    {
        return new self(Text::fromString($string), Style::default());
    }
}
