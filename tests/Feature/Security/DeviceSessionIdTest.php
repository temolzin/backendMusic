<?php

namespace Tests\Feature\Security;

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Str;
use ReflectionMethod;
use Tests\TestCase;

class DeviceSessionIdTest extends TestCase
{
    public function test_missing_session_id_falls_back_to_a_valid_one(): void
    {
        $this->assertSame(32, strlen($this->resolve(null)));
        $this->assertSame(32, strlen($this->resolve('')));
        $this->assertSame(32, strlen($this->resolve('   ')));
        $this->assertSame(32, strlen($this->resolve(['not', 'a', 'string'])));
    }

    public function test_session_id_longer_than_32_characters_is_replaced(): void
    {
        $this->assertSame(32, strlen($this->resolve(str_repeat('a', 33))));
        $this->assertSame(32, strlen($this->resolve(Str::uuid()->toString())));
    }

    public function test_valid_session_id_is_preserved(): void
    {
        $sessionId = 'abcdef0123456789abcdef0123456789';

        $this->assertSame($sessionId, $this->resolve($sessionId));
        $this->assertSame($sessionId, $this->resolve('  ' . $sessionId . '  '));
    }

    private function resolve($value): string
    {
        $method = new ReflectionMethod(PaymentController::class, 'resolveDeviceSessionId');
        $method->setAccessible(true);

        return $method->invoke(new PaymentController(), $value);
    }
}
