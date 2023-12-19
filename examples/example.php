<?php

use Aspectus\Component;
use Aspectus\Message;
use Aspectus\Terminal\TerminalDevice;
use Aspectus\Terminal\Xterm;

require_once \dirname(__DIR__) . '/vendor/autoload.php';

$mainComponent = new class implements Component
{
    public function view(): string
    {
        return '';
    }

    public function update(?Message $message): ?Message
    {
        return null;
    }
};

$xterm = new Xterm(new TerminalDevice());

(new Aspectus($xterm, $mainComponent, handleInput: true))
    ->start();
