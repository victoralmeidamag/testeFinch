<?php

namespace App\Adapters\Controllers;

use App\Adapters\Http\Request;
use App\Adapters\Http\DTO\TransferInputDTO;
use App\Application\UseCases\TransferUseCase;

class TransferController
{
    protected $pdo;
    public function __construct(
        private TransferUseCase $useCase
    ) {}

    public function handle(Request $request): void
    {
        header('Content-Type: application/json');

        try {
            
        if (
            is_null($request->input('from')) ||
            is_null($request->input('to')) ||
            is_null($request->input('amount'))
        ) {
            throw new \InvalidArgumentException("Campos obrigatórios não informados.");
        }

            $dto = new TransferInputDTO(
                (int)$request->input('from'),
                (int)$request->input('to'),
                (float)$request->input('amount')
            );

            $this->useCase->execute($dto);

            echo json_encode([
                'success' => true,
                'message' => "Transferência realizada com sucesso"
            ]);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
