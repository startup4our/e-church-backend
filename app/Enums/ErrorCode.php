<?php

namespace App\Enums;

enum ErrorCode: string
{
    // Authentication & Authorization
    case UNAUTHORIZED = 'UNAUTHORIZED';
    case FORBIDDEN = 'FORBIDDEN';
    case TOKEN_EXPIRED = 'TOKEN_EXPIRED';
    case INVALID_CREDENTIALS = 'INVALID_CREDENTIALS';
    
    // Validation
    case VALIDATION_ERROR = 'VALIDATION_ERROR';
    case REQUIRED_FIELD_MISSING = 'REQUIRED_FIELD_MISSING';
    case INVALID_FORMAT = 'INVALID_FORMAT';
    
    // Resource Management
    case RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    case RESOURCE_ALREADY_EXISTS = 'RESOURCE_ALREADY_EXISTS';
    case RESOURCE_CONFLICT = 'RESOURCE_CONFLICT';
    
    // Business Logic
    case BUSINESS_RULE_VIOLATION = 'BUSINESS_RULE_VIOLATION';
    case SCHEDULE_CONFLICT = 'SCHEDULE_CONFLICT';
    case PERMISSION_DENIED = 'PERMISSION_DENIED';
    
    // System
    case INTERNAL_SERVER_ERROR = 'INTERNAL_SERVER_ERROR';
    case SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE';
    case DATABASE_ERROR = 'DATABASE_ERROR';
    
    // Church-specific
    case CHURCH_NOT_FOUND = 'CHURCH_NOT_FOUND';
    case AREA_NOT_FOUND = 'AREA_NOT_FOUND';
    case AREA_HAS_USERS = 'AREA_HAS_USERS';
    case USER_NOT_IN_CHURCH = 'USER_NOT_IN_CHURCH';
    case SCHEDULE_ALREADY_EXISTS = 'SCHEDULE_ALREADY_EXISTS';
    
    public function getHttpStatusCode(): int
    {
        return match($this) {
            self::UNAUTHORIZED, self::TOKEN_EXPIRED, self::INVALID_CREDENTIALS => 401,
            self::FORBIDDEN, self::PERMISSION_DENIED => 403,
            self::RESOURCE_NOT_FOUND, self::CHURCH_NOT_FOUND, self::AREA_NOT_FOUND => 404,
            self::VALIDATION_ERROR, self::REQUIRED_FIELD_MISSING, self::INVALID_FORMAT => 422,
            self::RESOURCE_ALREADY_EXISTS, self::RESOURCE_CONFLICT, self::SCHEDULE_CONFLICT, 
            self::SCHEDULE_ALREADY_EXISTS, self::BUSINESS_RULE_VIOLATION, self::AREA_HAS_USERS => 409,
            self::SERVICE_UNAVAILABLE => 503,
            default => 500,
        };
    }
    
    public function getDefaultMessage(): string
    {
        return match($this) {
            self::UNAUTHORIZED => 'Não autorizado',
            self::FORBIDDEN => 'Acesso negado',
            self::TOKEN_EXPIRED => 'Token expirado',
            self::INVALID_CREDENTIALS => 'Credenciais inválidas',
            self::VALIDATION_ERROR => 'Dados inválidos',
            self::REQUIRED_FIELD_MISSING => 'Campo obrigatório ausente',
            self::INVALID_FORMAT => 'Formato inválido',
            self::RESOURCE_NOT_FOUND => 'Recurso não encontrado',
            self::RESOURCE_ALREADY_EXISTS => 'Recurso já existe',
            self::RESOURCE_CONFLICT => 'Conflito de recursos',
            self::BUSINESS_RULE_VIOLATION => 'Violação de regra de negócio',
            self::SCHEDULE_CONFLICT => 'Conflito de horário',
            self::PERMISSION_DENIED => 'Permissão negada',
            self::INTERNAL_SERVER_ERROR => 'Erro interno do servidor',
            self::SERVICE_UNAVAILABLE => 'Serviço indisponível',
            self::DATABASE_ERROR => 'Erro de banco de dados',
            self::CHURCH_NOT_FOUND => 'Igreja não encontrada',
            self::AREA_NOT_FOUND => 'Área não encontrada',
            self::AREA_HAS_USERS => 'Não é possível excluir uma área que possui usuários associados',
            self::USER_NOT_IN_CHURCH => 'Usuário não pertence à igreja',
            self::SCHEDULE_ALREADY_EXISTS => 'Escala já existe',
        };
    }
}