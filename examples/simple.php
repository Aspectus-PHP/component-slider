<?php

use Aspectus\Aspectus;
use Aspectus\Component;
use Aspectus\Components\Input\Slider;
use Aspectus\Components\Input\View\SliderView;
use Aspectus\Message;
use Aspectus\Terminal\TerminalDevice;
use Aspectus\Terminal\Xterm;

require_once \dirname(__DIR__) . '/vendor/autoload.php';

exec(command: 'stty -echo -icanon min 1 time 0 < /dev/tty', result_code: $resultCode);

$xterm = new Xterm(new TerminalDevice());

$mainComponent = new class($xterm) implements Component
{
    private Slider $slider;

    public function __construct(private Xterm $xterm)
    {
        $this->slider = new Slider($this->xterm, new SliderView(10, 5, 50));
    }

    public function view(): string
    {
        return $this->xterm
            ->moveCursorTo(4, 10)
            ->brightWhite()
            ->write('Use arrow keys or mouse to move the slider, any other key to quit!')
            ->write($this->slider->view());
    }

    public function update(?Message $message): ?Message
    {
        switch ($message?->type) {
            case Message::INIT:
                $this->xterm
                    ->saveCursorAndEnterAlternateScreenBuffer()
                    ->hideCursor()
                    ->flush();
                break;
            case Message::TERMINATE:
                $this->xterm
                    ->restoreCursorAndEnterNormalScreenBuffer()
                    ->showCursor()
                    ->flush();
                break;

            case Message::KEY_PRESS:
                if ($message['key'] === '<LEFT>') {
                    $this->slider->decrement(10);
                } elseif ($message['key'] === '<RIGHT>') {
                    $this->slider->increment(10);
                } else {
                    return Message::quit();
                }
            default:
                return null;
        }

        return null;
    }
};


(new Aspectus($xterm, $mainComponent, handleInput: true))
    ->start();
