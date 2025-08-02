<?php

declare(strict_types=1);

namespace Crumbls\Tui\Widgets;

use Crumbls\Tui\Widget;

class Navbar extends Widget
{
    public function setTabs(array $tabs): static
    {
        return $this->setAttribute('tabs', $tabs);
    }

    public function selected(int $index): static
    {
        return $this->setAttribute('selected', $index);
    }

    public function title(string $title): static
    {
        return $this->setAttribute('title', $title);
    }

    public function render(): string
    {
        $tabs = $this->getAttribute('tabs', []);
        $selected = $this->getAttribute('selected', 0);
        $title = $this->getAttribute('title', '');

        $output = '';

        // Top border
        $output .= '╔' . str_repeat('═', 78) . '╗' . "\n";

        // Title line
        if ($title) {
            $titleLine = '║ ' . str_pad($title, 76, ' ', STR_PAD_BOTH) . ' ║';
            $output .= $titleLine . "\n";
            $output .= '╠' . str_repeat('═', 78) . '╣' . "\n";
        }

        // Tabs line
        $tabsLine = '║ ';
        foreach ($tabs as $index => $tab) {
            if ($index === $selected) {
                $tabsLine .= "🔸[{$tab}] ";  // Current tab highlighted
            } else {
                $tabsLine .= "{$tab} ";      // Other tabs
            }
        }
        $tabsLine = str_pad($tabsLine, 77) . '║';
        $output .= $tabsLine . "\n";

        // Bottom border
        $output .= '╚' . str_repeat('═', 78) . '╝';

        return $output;
    }
}