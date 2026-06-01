<?php

namespace Installer;

use Composer\Script\Event;

class PostInstall
{
    public static function run(Event $event): void
    {
        $io = $event->getIO();
        $root = dirname(__DIR__, 2);

        $io->write('');
        $io->write('');
        $io->write('  ╔═══════════════════════════════════════════╗');
        $io->write('  ║     ✦ AuraPHP Framework — Installed ✦   ║');
        $io->write('  ╚═══════════════════════════════════════════╝');
        $io->write('');

        // —— .env ——
        $envFile = "{$root}/.env";
        $envExample = "{$root}/.env.example";
        if (!file_exists($envFile) && file_exists($envExample)) {
            copy($envExample, $envFile);
            $io->write('<info>  ✓ .env created from .env.example</info>');
        }

        // —— generate APP_KEY ——
        $keyFile = "{$root}/.env";
        if (file_exists($keyFile)) {
            $envContent = file_get_contents($keyFile);
            if (preg_match('/^APP_KEY=$/m', $envContent)) {
                $key = bin2hex(random_bytes(16));
                $envContent = preg_replace('/^APP_KEY=$/m', "APP_KEY={$key}", $envContent);
                file_put_contents($keyFile, $envContent);
                $io->write('<info>  ✓ APP_KEY generated</info>');
            }
        }

        $io->write('');
        $io->write('  <success>  Done! Your project is ready.</success>');
        $io->write('');

        // —— next steps ——
        $io->write('  ─── Next Steps ───');
        $io->write('');

        $io->write('  1. Start the dev server:');
        $io->write('     <comment>  php aura serve</comment>');
        $io->write('');

        $io->write('  2. Open in your browser:');
        $io->write('     <comment>  http://127.0.0.1:8080</comment>');
        $io->write('');

        $io->write('  3. Explore available commands:');
        $io->write('     <comment>  php aura list</comment>');
        $io->write('');

        $io->write('  ─── Available Commands ───');
        $io->write('');

        passthru('php aura list');
    }
}
