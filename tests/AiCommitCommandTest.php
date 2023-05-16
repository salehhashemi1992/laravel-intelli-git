<?php

namespace Salehhashemi\LaravelIntelliGit\Tests;

use Illuminate\Foundation\Console\Kernel;
use Salehhashemi\LaravelIntelliGit\LaravelIntelliGitServiceProvider;

class AiCommitCommandTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected function getPackageProviders($app): array
    {
        return [LaravelIntelliGitServiceProvider::class];
    }

    /** @test */
    public function test_ai_commit_command_is_registered()
    {
        $kernel = $this->app->make(Kernel::class);

        $commandList = $kernel->all();

        $this->assertArrayHasKey('ai:commit', $commandList);
    }
}
