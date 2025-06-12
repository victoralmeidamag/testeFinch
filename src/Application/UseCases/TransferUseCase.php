<?php


namespace App\Application\UseCases;


use App\Domain\Repositories\Account\AccountRepositoryInterface;
use App\Adapters\Http\DTO\TransferInputDTO;
use App\Domain\Services\Transfer\TransferInterface;

class TransferUseCase
{
    public function __construct(
        private AccountRepositoryInterface $repository,
        private TransferInterface $service
    ) {}

    public function execute(TransferInputDTO $dto): void
    {
        if ($dto->fromId === $dto->toId) {
            throw new \DomainException("Conta de origem e destino devem ser diferentes.");
        }

        if ($dto->amount <= 0) {
            throw new \DomainException("Valor da transferência deve ser maior que zero.");
        }

        $this->repository->beginTransaction();

        try {

            [$firstId, $secondId] = $dto->fromId < $dto->toId
                ? [$dto->fromId, $dto->toId]
                : [$dto->toId, $dto->fromId];

            $first  = $this->repository->findAndLock($firstId);
            $second = $this->repository->findAndLock($secondId);

            $from = $dto->fromId === $firstId ? $first : $second;
            $to   = $dto->toId === $secondId ? $second : $first;
            
            $this->service->execute($from, $to, $dto->amount);

            $this->repository->save($from);
            $this->repository->save($to);
            $this->repository->logTransaction($from->id, $to->id, $dto->amount);

            $this->repository->commit();
        } catch (\Throwable $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

}
