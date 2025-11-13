<?php

namespace App\Traits;

use App\Exceptions\DomainException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorizedException;

trait ThrowsExceptions
{
    protected function throwDomain(string $message): never
    {
        throw new DomainException($message);
    }

    protected function throwNotFound(string $message = 'Recurso não encontrado'): never
    {
        throw new NotFoundException($message);
    }

    protected function throwUnauthorized(string $message = 'Acesso não autorizado'): never
    {
        throw new UnauthorizedException($message);
    }

    protected function throwForbidden(string $message = 'Acesso proibido'): never
    {
        throw new ForbiddenException($message);
    }
}
