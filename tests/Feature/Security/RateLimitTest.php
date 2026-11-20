<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    public function test_authenticated_requests_get_a_higher_limit_than_guests(): void
    {
        $limiter = RateLimiter::limiter('api');

        $this->assertSame(60, $limiter(Request::create('/api/me'))->maxAttempts);

        $request = Request::create('/api/me');
        $request->setUserResolver(fn () => new User(['id' => 123]));

        $this->assertSame(300, $limiter($request)->maxAttempts);
    }
}
