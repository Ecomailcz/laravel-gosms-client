<?php

declare(strict_types = 1);

use EcomailGoSms\Tests\TestCase;
use Illuminate\Support\Facades\Http;

uses(TestCase::class)->in(__DIR__);

beforeEach(function (): void {
    Http::preventStrayRequests();
});
