<?php

namespace App\Http\Controllers\Traits\Swagger;

/**
 * @OA\Tag(
 *     name="Medical Certificates",
 *     description="Gerenciamento de atestados médicos"
 * )
 */
trait SwaggerMedicalCertificateDocs
{
    /**
     * @OA\Get(
     *     path="/api/medical-certificates",
     *     summary="Listar todos os atestados médicos",
     *     description="Retorna lista paginada de atestados médicos com filtros opcionais",
     *     operationId="getMedicalCertificates",
     *     tags={"Medical Certificates"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Buscar por conteúdo, hash de validação, nome do paciente ou médico",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="customer",
     *         in="query",
     *         description="Filtrar por ID do paciente ('all' para todos)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por status ('all', 'valid', 'expired')",
     *         required=false,
     *         @OA\Schema(type="string", enum={"all", "valid", "expired"}, default="all")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de atestados retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MedicalCertificate")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function index() {}

    /**
     * @OA\Post(
     *     path="/api/medical-certificates",
     *     summary="Criar novo atestado médico",
     *     description="Cria um novo atestado médico. Apenas médicos ou administradores com CRM cadastrado podem criar.",
     *     operationId="createMedicalCertificate",
     *     tags={"Medical Certificates"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_customer", "content", "days_off", "valid_until"},
     *             @OA\Property(property="id_customer", type="integer", example=1, description="ID do paciente"),
     *             @OA\Property(property="content", type="string", maxLength=1000, example="Atesto para os devidos fins que o paciente necessita de repouso médico", description="Conteúdo do atestado"),
     *             @OA\Property(property="days_off", type="integer", minimum=0, maximum=365, example=3, description="Número de dias de afastamento"),
     *             @OA\Property(property="valid_until", type="string", format="date", example="2025-01-25", description="Data de validade do atestado (máximo 1 ano)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Atestado criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/MedicalCertificate")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação ou CRM não cadastrado"),
     *     @OA\Response(response=403, description="Apenas médicos ou administradores podem emitir atestados"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function store() {}

    /**
     * @OA\Get(
     *     path="/api/medical-certificates/{medicalCertificate}",
     *     summary="Visualizar atestado específico",
     *     description="Retorna detalhes de um atestado médico específico",
     *     operationId="showMedicalCertificate",
     *     tags={"Medical Certificates"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="medicalCertificate",
     *         in="path",
     *         description="ID do atestado",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Atestado encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/MedicalCertificate")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Atestado não encontrado"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function show() {}

    /**
     * @OA\Delete(
     *     path="/api/medical-certificates/{medicalCertificate}",
     *     summary="Deletar atestado",
     *     description="Remove um atestado do sistema (soft delete)",
     *     operationId="deleteMedicalCertificate",
     *     tags={"Medical Certificates"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="medicalCertificate",
     *         in="path",
     *         description="ID do atestado",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Atestado deletado com sucesso"
     *     ),
     *     @OA\Response(response=404, description="Atestado não encontrado"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function destroy() {}

    /**
     * @OA\Get(
     *     path="/api/medical-certificates/verify/{hash}",
     *     summary="Verificar autenticidade do atestado",
     *     description="Verifica a autenticidade de um atestado médico através do hash de validação. Endpoint público que não requer autenticação.",
     *     operationId="verifyMedicalCertificate",
     *     tags={"Medical Certificates"},
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         description="Hash de validação do atestado",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verificação realizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="certificate", ref="#/components/schemas/MedicalCertificate"),
     *             @OA\Property(property="isValid", type="boolean", example=true, description="Indica se o atestado é válido (não adulterado)"),
     *             @OA\Property(property="isExpired", type="boolean", example=false, description="Indica se o atestado está expirado"),
     *             @OA\Property(property="verificationDate", type="string", format="date-time", example="2025-01-20T10:30:00Z", description="Data/hora da verificação")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Atestado não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="certificate", type="null"),
     *             @OA\Property(property="isValid", type="boolean", example=false),
     *             @OA\Property(property="isExpired", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Atestado não encontrado no sistema.")
     *         )
     *     )
     * )
     */
    public function verify() {}
}
