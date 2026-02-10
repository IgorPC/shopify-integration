<?php

namespace Tests\Feature\Repositories;

use App\Http\DTOs\PersistLogDTO;
use App\Http\Enums\LogActionEnum;
use App\Http\Enums\LogTypeEnum;
use App\Http\Repositories\SystemLogRepository;
use App\Models\SystemLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemLogRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SystemLogRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new SystemLogRepository(new SystemLog());
    }

    /**
     * Test persistLog:
     * Verifies if the DTO data is correctly saved in the database.
     */
    public function test_it_persists_log_correctly(): void
    {
        $dto = new PersistLogDTO(
            action: LogActionEnum::CREATE,
            type: LogTypeEnum::PRODUCT,
            target: 'gid://shopify/Product/12345',
            payload: 'Product successfully synced',
        );

        $this->repository->persistLog($dto);

        $this->assertDatabaseHas('system_logs', [
            'action' => 'create',
            'type'   => 'product',
            'target' => 'gid://shopify/Product/12345',
            'status' => 1,
        ]);

        $log = SystemLog::first();
        $this->assertEquals('Product successfully synced', $log->payload);
    }

    /**
     * Test persistLog integrity:
     * Ensures it fails if required data is missing (Integration with DB constraints).
     */
    public function test_it_throws_exception_on_missing_data(): void
    {
        $this->expectException(\TypeError::class);
        $this->repository->persistLog(null);
    }

    /**
     * Verifies if it returns pagination structure and count.
     */
    public function test_it_returns_paginated_logs(): void
    {
        SystemLog::create([
            'action' => 'create',
            'type' => 'product',
            'target' => 'id-1',
            'payload' => 'Payload 1',
            'status' => true,
            'created_at' => now()->subMinutes(10),
        ]);

        SystemLog::create([
            'action' => 'update',
            'type' => 'product',
            'target' => 'id-2',
            'payload' => 'Payload 2',
            'status' => true,
            'created_at' => now(),
        ]);


        $paginated = $this->repository->getSystemLogPaginated(1, 1);

        $this->assertCount(1, $paginated->items());
        $this->assertEquals(2, $paginated->total());
        $this->assertEquals(1, $paginated->currentPage());
        $this->assertEquals('create', $paginated->items()[0]->action);
    }

    /**
     * getSystemLogPaginated ordering: Must be ASC by created_at.
     */
    public function test_it_returns_logs_ordered_by_created_at_asc(): void
    {
        SystemLog::create([
            'action' => 'new',
            'type' => 'product',
            'payload' => 'new',
            'status' => true,
            'created_at' => now(),
        ]);

        SystemLog::create([
            'action' => 'old',
            'type' => 'product',
            'payload' => 'old',
            'status' => true,
            'created_at' => now()->subDays(2),
        ]);

        $paginated = $this->repository->getSystemLogPaginated(10, 1);
        $items = $paginated->items();
        
        $this->assertEquals('new', $items[0]->action);
        $this->assertEquals('old', $items[1]->action);
    }

    /**
     * Test field selection: Ensures only the requested fields are in the result.
     */
    public function test_it_returns_only_selected_fields(): void
    {
        SystemLog::create([
            'action' => 'create',
            'type' => 'product',
            'payload' => 'test',
            'status' => true,
        ]);

        $paginated = $this->repository->getSystemLogPaginated(1, 1);
        $log = $paginated->items()[0];

        $attributes = $log->getAttributes();

        $this->assertArrayHasKey('action', $attributes);
        $this->assertArrayHasKey('type', $attributes);
        $this->assertArrayHasKey('payload', $attributes);
        $this->assertArrayHasKey('status', $attributes);
        $this->assertArrayHasKey('created_at', $attributes);
        $this->assertArrayNotHasKey('id', $attributes);
    }
}
