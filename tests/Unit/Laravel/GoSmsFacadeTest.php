<?php

declare(strict_types = 1);

namespace EcomailGoSms\Tests\Unit\Laravel;

use EcomailGoSms\Laravel\GoSmsFacade;

it('uses gosms facade accessor', function (): void {
    $method = new \ReflectionMethod(GoSmsFacade::class, 'getFacadeAccessor');

    expect($method->invoke(null))->toBe('gosms');
});
