<?php

namespace Tests\Unit;

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
}
