<?php

namespace App\Http\Controllers\Traits\Swagger;

/**
 * @OA\Tag(
 *     name="Medical Records",
 *     description="Gerenciamento de prontuários médicos"
 * )
 */
trait SwaggerMedicalRecordDocs
{
    /**
     * @OA\Get(
     *     path="/api/medical-records",
     *     summary="Listar todos os prontuários",
     *     description="Retorna lista paginada de prontuários médicos. Acesso restrito a médicos e administradores.",
     *     operationId="getMedicalRecords",
     *     tags={"Medical Records"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Itens por página",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Buscar por nome, email ou telefone do paciente",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Data inicial do período",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Data final do período",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de prontuários retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MedicalRecord")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Acesso negado. Apenas médicos podem acessar prontuários")
     * )
     */
    public function index() {}

    /**
     * @OA\Get(
     *         path="/api/medical-records/{id}",
     *     summary="Visualizar prontuário específico",
     *     description="Retorna detalhes de um prontuário médico específico por ID. Acesso restrito a médicos e administradores.",
     *     operationId="showMedicalRecord",
     *     tags={"Medical Records"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do prontuário",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prontuário encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/MedicalRecord")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Prontuário não encontrado"),
     *     @OA\Response(response=403, description="Acesso negado. Apenas médicos podem visualizar prontuários."),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function showById() {}

    /**
     * @OA\Get(
     *     path="/api/medical-records/customer/{customer}",
     *     summary="Listar prontuários de um paciente",
     *     description="Retorna todos os prontuários de um paciente específico",
     *     operationId="getMedicalRecordsByCustomer",
     *     tags={"Medical Records"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="customer",
     *         in="path",
     *         description="ID do cliente/paciente",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prontuários do paciente retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="customer", ref="#/components/schemas/Customer"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MedicalRecord")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Acesso negado")
     * )
     */
    public function show() {}

    /**
     * @OA\Post(
     *     path="/api/medical-records",
     *     summary="Criar novo prontuário",
     *     description="Cria um novo registro de prontuário médico. Apenas médicos com CRM cadastrado podem criar.",
     *     operationId="createMedicalRecord",
     *     tags={"Medical Records"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_customer", "chief_complaint", "physical_exam", "diagnosis", "treatment_plan"},
     *             @OA\Property(property="id_customer", type="integer", example=1, description="ID do paciente"),
     *             @OA\Property(property="id_appointment", type="integer", example=1, description="ID da consulta (opcional)"),
     *             @OA\Property(property="chief_complaint", type="string", maxLength=1000, example="Dor de cabeça intensa há 3 dias"),
     *             @OA\Property(property="physical_exam", type="string", maxLength=2000, example="Pressão arterial: 120/80 mmHg. Temperatura: 36.5°C"),
     *             @OA\Property(property="diagnosis", type="string", maxLength=1000, example="Enxaqueca crônica"),
     *             @OA\Property(property="treatment_plan", type="string", maxLength=2000, example="Repouso e medicação analgésica"),
     *             @OA\Property(property="prescriptions", type="string", maxLength=2000, example="Paracetamol 500mg, 1 comprimido a cada 6 horas"),
     *             @OA\Property(property="observations", type="string", maxLength=1000, example="Paciente relatou melhora após primeira dose"),
     *             @OA\Property(property="follow_up_date", type="string", format="date", example="2025-02-01", description="Data de retorno"),
     *             @OA\Property(property="medical_history", type="string", example="Hipertensão arterial controlada"),
     *             @OA\Property(property="allergies", type="string", example="Alergia a dipirona"),
     *             @OA\Property(property="medications", type="string", example="Losartana 50mg 1x ao dia")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Prontuário criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/MedicalRecord")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação ou CRM não cadastrado"),
     *     @OA\Response(response=403, description="Apenas médicos podem criar prontuários"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function store() {}

    /**
     * @OA\Put(
     *     path="/api/medical-records/{medicalRecord}",
     *     summary="Atualizar prontuário",
     *     description="Atualiza um registro de prontuário existente. Apenas o médico responsável ou administrador pode editar.",
     *     operationId="updateMedicalRecord",
     *     tags={"Medical Records"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="medicalRecord",
     *         in="path",
     *         description="ID do prontuário",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="chief_complaint", type="string", maxLength=1000),
     *             @OA\Property(property="physical_exam", type="string", maxLength=2000),
     *             @OA\Property(property="diagnosis", type="string", maxLength=1000),
     *             @OA\Property(property="treatment_plan", type="string", maxLength=2000),
     *             @OA\Property(property="prescriptions", type="string", maxLength=2000),
     *             @OA\Property(property="observations", type="string", maxLength=1000),
     *             @OA\Property(property="follow_up_date", type="string", format="date"),
     *             @OA\Property(property="medical_history", type="string"),
     *             @OA\Property(property="allergies", type="string"),
     *             @OA\Property(property="medications", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prontuário atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/MedicalRecord")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Prontuário não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=403, description="Apenas o médico responsável pode editar"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function update() {}

    /**
     * @OA\Delete(
     *     path="/api/medical-records/{medicalRecord}",
     *     summary="Deletar prontuário",
     *     description="Remove um prontuário do sistema (soft delete). Apenas o médico responsável ou administrador pode deletar.",
     *     operationId="deleteMedicalRecord",
     *     tags={"Medical Records"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="medicalRecord",
     *         in="path",
     *         description="ID do prontuário",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Prontuário deletado com sucesso"
     *     ),
     *     @OA\Response(response=404, description="Prontuário não encontrado"),
     *     @OA\Response(response=403, description="Apenas o médico responsável pode deletar"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function destroy() {}

    /**
     * @OA\Get(
     *     path="/api/medical-records/customer/{customerId}",
     *     summary="Listar prontuários de um paciente",
     *     description="Retorna lista paginada de todos os prontuários médicos de um paciente específico. Acesso restrito a médicos e administradores.",
     *     operationId="getMedicalRecordsByCustomerId",
     *     tags={"Medical Records"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="customerId",
     *         in="path",
     *         description="ID do paciente",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Itens por página",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de prontuários do paciente retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MedicalRecord")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Acesso negado. Apenas médicos podem acessar prontuários."),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function getByCustomer() {}
}
