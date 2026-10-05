<?php

declare(strict_types=1);

namespace App\Command;

use Eva\Console\ArgvInput;
use Eva\Database\ConnectionStoreInterface;
use Tests\Integrations\Seed\MainTestSeed;

readonly class SeedDefaultCommand
{
    public function __construct(
        private ConnectionStoreInterface $connectionStore,
    ) {}

    public function execute(ArgvInput $argvInput): void
    {
        try {
            MainTestSeed::init($this->connectionStore);
            print "Done.\n";
        } catch (\Throwable $e) {
            print_r($e);
        }
    }
}