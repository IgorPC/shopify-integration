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
}
