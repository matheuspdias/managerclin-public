<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\Swagger\SwaggerSchemas;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Clínica Agenda API",
 *     description="API REST para gerenciamento de clínicas médicas - Agendamentos, Pacientes, Prontuários e Finanças",
 *     @OA\Contact(
 *         email="contato@clinica-agenda.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="/",
 *     description="Servidor de API"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Autenticação via token Bearer. Use o formato: Bearer {token}"
 * )
 *
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     title="Erro de Validação",
 *     @OA\Property(property="message", type="string", example="Os dados fornecidos são inválidos."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         example={"email": {"O campo email é obrigatório."}}
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Error",
 *     type="object",
 *     title="Erro Genérico",
 *     @OA\Property(property="message", type="string", example="Recurso não encontrado")
 * )
 */
abstract class Controller
{
    use SwaggerSchemas;
}
