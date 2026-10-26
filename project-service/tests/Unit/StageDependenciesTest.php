<?php

namespace Tests\Unit;

use App\Exceptions\Domain\ValidationException;
use App\Support\StageDependencies;
use Tests\TestCase;

class StageDependenciesTest extends TestCase
{
    public function test_accepts_acyclic_graphs(): void
    {
        // Chain 3 -> 2 -> 1, plus an independent stage.
        StageDependencies::assertAcyclic([1 => null, 2 => 1, 3 => 2, 4 => null]);

        $this->assertTrue(true);
    }

    public function test_rejects_self_reference(): void
    {
        $this->expectException(ValidationException::class);

        StageDependencies::assertAcyclic([1 => null, 2 => 2]);
    }

    public function test_rejects_two_node_cycle(): void
    {
        $this->expectException(ValidationException::class);

        StageDependencies::assertAcyclic([1 => 2, 2 => 1]);
    }

    public function test_rejects_longer_cycle(): void
    {
        $this->expectException(ValidationException::class);

        StageDependencies::assertAcyclic([1 => 2, 2 => 3, 3 => 4, 4 => 2, 5 => null]);
    }

    public function test_rejects_cycle_reaching_back_to_start(): void
    {
        $this->expectException(ValidationException::class);

        StageDependencies::assertAcyclic([1 => 5, 2 => 1, 3 => 2, 4 => 3, 5 => 4]);
    }
}
