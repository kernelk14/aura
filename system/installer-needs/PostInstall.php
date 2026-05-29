<?php

namespace Installer;
use Composer\Script\Event;
class PostInstall {
    public static function run(Event $event) {
        $io = $event->getIO();
        $io->write("\n\n");
        $io->write("<success>Done initializing your project!</success>");
        $io->write("<success>Welcome to the Aura PHP Framework!</success>");
        $io->write("<info>Get started with `<success>aura</success>`, our cli tool:</info>");
        $io->write("> php aura list");
        passthru('php aura list');
    }
}

?>
