<?php

namespace App\Exceptions;

use App\Enums\ErrorCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppException extends \Exception
{
    protected ErrorCode $errorCode;
    protected array $details;
    protected ?string $userMessage;
    
    public function __construct(
        ErrorCode $errorCode,
        ?string $message = null,
        array $details = [],
        ?string $userMessage = null,
        \Throwable $previous = null
    ) {
        $this->errorCode = $errorCode;
        $this->details = $details;
        $this->userMessage = $userMessage;
        
        parent::__construct(
            $message ?? $errorCode->getDefaultMessage(),
            $errorCode->getHttpStatusCode(),
            $previous
        );
    }
    
    public function getErrorCode(): ErrorCode
    {
        return $this->errorCode;
    }
    
    public function getDetails(): array
    {
        return $this->details;
    }
    
    public function getUserMessage(): ?string
    {
        return $this->userMessage;
    }
    
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => $this->errorCode->value,
                'message' => $this->userMessage ?? $this->errorCode->getDefaultMessage(),
                'details' => $this->details,
                'timestamp' => now()->toISOString(),
            ]
        ], $this->errorCode->getHttpStatusCode());
    }
}

