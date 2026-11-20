<?php

namespace Tests\Unit;

use App\Services\PaymentFailureReference;
use RuntimeException;
use Tests\TestCase;

class PaymentFailureReferenceTest extends TestCase
{
    public function test_failure_references_include_the_step_and_are_unique(): void
    {
        $exception = new RuntimeException('Sensitive internal detail');

        $firstReference = PaymentFailureReference::for('OPENPAY', $exception);
        $secondReference = PaymentFailureReference::for('OPENPAY', $exception);

        $this->assertStringStartsWith('OPENPAY-RuntimeException-', $firstReference);
        $this->assertNotSame($firstReference, $secondReference);
        $this->assertStringNotContainsString('Sensitive internal detail', $firstReference);
    }
}
