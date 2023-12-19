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
        $this->slider = new Slider($this->xterm, new SliderView(10, 5, 50, showValue: true));
    }

    public function view(): string
    {
        return $this->xterm
            ->moveCursorTo(4, 10)
            ->brightWhite()
            ->write('Use arrow keys or use mouse button 1 to move the slider, Q to quit!')
            ->moveCursorTo(5, 15)
            ->white()
            ->write('Mouse click button 1 and hold to drag slider')
            ->write($this->slider->view());
    }

    public function update(?Message $message): ?Message
    {
        switch ($message?->type) {
            case Message::INIT:
                $this->xterm
                    ->saveCursorAndEnterAlternateScreenBuffer()
                    ->hideCursor()
                    ->setPrivateModeTrackMouseAll()
                    ->flush();
                break;
            case Message::TERMINATE:
                $this->xterm
                    ->restoreCursorAndEnterNormalScreenBuffer()
                    ->showCursor()
                    ->unsetPrivateModeTrackMouseAll()
                    ->flush();
                break;
            case Message::KEY_PRESS:
                if (strtolower($message['key']) === 'q') {
                    return Message::quit();
                }
                return $this->slider->update($message);
            case Message::MOUSE_INPUT:
                return $this->slider->update($message);

            default:
                return null;
        }

        return null;
    }
};


(new Aspectus($xterm, $mainComponent, handleInput: true, handleMouseInput: true))
    ->start();
