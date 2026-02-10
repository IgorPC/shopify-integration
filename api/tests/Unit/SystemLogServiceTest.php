<?php

namespace Tests\Unit;

use App\Http\DTOs\Responses\PaginatedResponseDTO;
use App\Http\DTOs\SystemLogDTO;
use App\Models\SystemLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;
use App\Http\Services\SystemLogService;
use App\Http\Repositories\SystemLogRepository;
use App\Http\DTOs\PersistLogDTO;
use App\Http\Enums\LogActionEnum;
use App\Http\Enums\LogTypeEnum;
use Mockery;

class SystemLogServiceTest extends TestCase
{
    private SystemLogRepository $repositoryMock;
    private SystemLogService $systemLogService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repositoryMock = Mockery::mock(SystemLogRepository::class);
        $this->systemLogService = new SystemLogService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test the persist method:
     * It should receive a PersistLogDTO and pass it correctly to the repository's persistLog method.
     */
    public function test_persist_should_call_repository_with_correct_dto(): void
    {
        $dto = new PersistLogDTO(
            LogActionEnum::CREATE,
            LogTypeEnum::PRODUCT,
            'gid://shopify/Product/123',
            'Successfully persisted log test'
        );
        $this->repositoryMock
            ->shouldReceive('persistLog')
            ->once()
            ->with($dto)
            ->andReturnNull();
        $this->systemLogService->persist($dto);

        $this->assertTrue(true);
    }

    /**
     * Test the pagination method with correct types.
     */
    public function test_get_system_log_paginated_returns_correct_structure(): void
    {
        $perPage = 5;
        $currentPage = 1;

        $logModel = new SystemLog([
            'action' => LogActionEnum::CREATE->value,
            'type' => LogTypeEnum::PRODUCT->value,
            'identifier' => '123',
            'payload' => 'Test Log',
            'status' => true,
        ]);
        $logModel->created_at = now();

        $paginatorMock = Mockery::mock(LengthAwarePaginator::class);
        $paginatorMock->shouldReceive('getCollection')->once()->andReturn(collect([$logModel]));
        $paginatorMock->shouldReceive('currentPage')->andReturn($currentPage);
        $paginatorMock->shouldReceive('lastPage')->andReturn(1);
        $paginatorMock->shouldReceive('total')->andReturn(1);

        $this->repositoryMock
            ->shouldReceive('getSystemLogPaginated')
            ->once()
            ->with($perPage, $currentPage)
            ->andReturn($paginatorMock);

        $result = $this->systemLogService->getSystemLogPaginated($perPage, $currentPage);

        $this->assertInstanceOf(PaginatedResponseDTO::class, $result);
        $this->assertCount(1, $result->items);
        $this->assertInstanceOf(SystemLogDTO::class, $result->items[0]);
    }

    /**
     * Test pagination with empty results.
     */
    public function test_get_system_log_paginated_handles_empty_results(): void
    {
        $paginatorMock = Mockery::mock(LengthAwarePaginator::class);
        $paginatorMock->shouldReceive('getCollection')->andReturn(collect([]));
        $paginatorMock->shouldReceive('currentPage')->andReturn(1);
        $paginatorMock->shouldReceive('lastPage')->andReturn(0);
        $paginatorMock->shouldReceive('total')->andReturn(0);

        $this->repositoryMock
            ->shouldReceive('getSystemLogPaginated')
            ->andReturn($paginatorMock);

        $result = $this->systemLogService->getSystemLogPaginated();

        $this->assertEmpty($result->items);
        $this->assertEquals(0, $result->total);
    }
}
