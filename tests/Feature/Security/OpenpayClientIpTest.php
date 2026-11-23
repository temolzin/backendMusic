<?php

namespace Tests\Feature\Security;

use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use ReflectionMethod;
use Tests\TestCase;

class OpenpayClientIpTest extends TestCase
{
    public function test_it_returns_the_request_ipv4_address(): void
    {
        $request = Request::create('/', 'POST', [], [], [], ['REMOTE_ADDR' => '203.0.113.12']);

        $this->assertSame('203.0.113.12', $this->resolve($request));
    }

    public function test_it_ignores_non_ipv4_addresses(): void
    {
        $request = Request::create('/', 'POST', [], [], [], ['REMOTE_ADDR' => '2001:db8::1']);

        $this->assertNull($this->resolve($request));
    }

    private function resolve(Request $request): ?string
    {
        $method = new ReflectionMethod(PaymentController::class, 'resolveOpenpayClientIp');
        $method->setAccessible(true);

        return $method->invoke(new PaymentController(), $request);
    }
}
