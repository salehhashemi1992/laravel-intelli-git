<?php

namespace Salehhashemi\LaravelIntelliGit\Tests;

use Mockery\MockInterface;
use Orchestra\Testbench\TestCase;
use Salehhashemi\LaravelIntelliGit\OpenAi;

abstract class BaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(OpenAi::class, function (MockInterface $mock) {
            $mock->shouldReceive('execute')->zeroOrMoreTimes()->andReturn('Output');
        });
    }
}
