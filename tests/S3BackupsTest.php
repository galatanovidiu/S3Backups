<?php

namespace Tests\Feature;

use Orchestra\Testbench\TestCase;

class S3BackupsTest extends TestCase
{

    public function setUp():void
    {
        parent::setUp();

        // This fixed the issue
        $this->withoutMockingConsoleOutput();
    }


    public function test_it_returns_a_0_exit_code()
    {
        $this->artisan('s3Backup:run')->assertExitCode(0);
    }
}
