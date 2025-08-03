<?php

declare(strict_types=1);

namespace Crumbls\Tui\Extension\Core\Widget\Paragraph;

enum Wrap
{
    case None;
    case Word;
    case WordTrimmed;
    case Character;
}
