<?php

namespace Aspectus\Components\Input\View;

final class SliderView
{
    public function __construct(
        readonly public int $y,
        readonly public int $x,
        readonly public int $width,
        readonly public string $line = '─',
        readonly public string $marker = '█',
        readonly public string $left = '├',
        readonly public string $right = '┤',
        readonly public bool $showValue = false,
        readonly public bool $valuePositionLeft = false,
    ) {
    }

    public function render(int $value): string
    {
        $markerAt = (int) ($this->width * $value / 100);

        $emptyBlocksLeft = $markerAt < 1 ? '' : str_repeat($this->line, $markerAt);
        $emptyBlocksRight = str_repeat($this->line, $this->width - $markerAt);

        $view = $this->left . $emptyBlocksLeft . $this->marker . $emptyBlocksRight . $this->right;

        if ($this->showValue && $this->valuePositionLeft) {
            return str_pad($value, 3, ' ', STR_PAD_LEFT) . ' ' . $view;
        }

        if ($this->showValue && !$this->valuePositionLeft) {
            return $view . ' ' . str_pad($value, 3, ' ', STR_PAD_LEFT);
        }

        return $view;
    }
}