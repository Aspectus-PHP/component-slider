<?php

namespace Aspectus\Components\Input;

use Aspectus\Component;
use Aspectus\Components\Input\View\SliderView;
use Aspectus\Message;
use Aspectus\Terminal\Xterm;
use Aspectus\Terminal\Xterm\Event\MouseInputEvent;

class Slider implements Component
{
    public function __construct(
        private Xterm $xterm,
        private SliderView $view = new SliderView(),
        private int $value = 50,
        private string $decrementKey = '<LEFT>',        // todo: support array here
        private string $incrementKey = '<RIGHT>',        // todo: support array here
        private int $step = 1
    ) {
    }


    public function setValue(int $value): void
    {
        $this->value = min(max(0, $value), 100);
    }

    public function increment(int $value = 1): void
    {
        $this->value = min($this->value + $value, 100);
    }

    public function decrement(int $value = 1): void
    {
        $this->value = max(0, $this->value - $value);
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function view(): string
    {
        return $this->xterm
            ->moveCursorTo($this->view->y, $this->view->x)
            ->eraseLine()
            ->write($this->view->render($this->value));
    }

    public function update(?Message $message): ?Message
    {
        if ($message === null) {
            return null;
        }

        return match ($message->type) {
            Message::MOUSE_INPUT => $this->handleMouseInput($message['event']),
            Message::KEY_PRESS => $this->handleKeyPress($message['key']),
            default => null
        };
    }

    private function handleKeyPress(string $key): ?Message
    {
        if ($key === $this->decrementKey) {
            $this->decrement($this->step);
        }

        if ($key === $this->incrementKey) {
            $this->increment($this->step);
        }

        return null;
    }

    private function handleMouseInput(MouseInputEvent $event): ?Message
    {
        if (!$event->button1() || $event->y !== $this->view->y) {
            return null;
        }

        $viewValueClicked = $event->x - $this->view->x - strlen($this->view->left) + 2;
        if ($viewValueClicked > $this->view->width) {
            return null;
        }

        $this->setValue((int) ($viewValueClicked * 100 / ($this->view->width)));

        return null;
    }
}