<?php

use Aspectus\Aspectus;
use Aspectus\Components\Basic\DefaultMainComponent;
use Aspectus\Components\Input\Slider;
use Aspectus\Components\Input\View\SliderView;
use Aspectus\Message;
use Aspectus\Terminal\TerminalDevice;
use Aspectus\Terminal\Xterm;

require_once \dirname(__DIR__) . '/vendor/autoload.php';

exec(command: 'stty -echo -icanon min 1 time 0 < /dev/tty', result_code: $resultCode);

$xterm = new Xterm(new TerminalDevice());

$mainComponent = new class($xterm) extends DefaultMainComponent
{
    private Slider $slider;

    public function __construct(protected Xterm $xterm)
    {
        $this->slider = new Slider($this->xterm, new SliderView(10, 5, 50, showValue: true));
        parent::__construct($this->xterm);
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
        return match ($message?->type) {
            Message::KEY_PRESS => match ($message['key']) {
                'q' => Message::quit(),
                default => $this->slider->update($message),
            },
            Message::MOUSE_INPUT => $this->slider->update($message),
            default => parent::update($message)
        };
    }

    protected function onInit(Aspectus $aspectus): ?Message
    {
        $this->xterm->setPrivateModeTrackMouseAll();
        return parent::onInit($aspectus);
    }

    protected function onTerminate(Aspectus $aspectus): ?Message
    {
        $this->xterm->unsetPrivateModeTrackMouseAll();
        return parent::onTerminate($aspectus);
    }
};


(new Aspectus($xterm, $mainComponent, handleInput: true, handleMouseInput: true))
    ->start();
