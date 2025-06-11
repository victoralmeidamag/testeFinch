<?php

namespace App\Adapters\Http\DTO;

class TransferInputDTO
{
    public function __construct(
        public int $fromId,
        public int $toId,
        public float $amount
    ) {}
}