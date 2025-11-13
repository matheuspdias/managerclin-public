<?php

namespace App\Http\Controllers\Traits\Swagger;

/**
 * @OA\Schema(
 *     schema="Customer",
 *     type="object",
 *     title="Cliente/Paciente",
 *     description="Modelo de cliente/paciente do sistema",
 *     @OA\Property(property="id", type="integer", example=1, description="ID do cliente"),
 *     @OA\Property(property="name", type="string", example="João Silva", description="Nome completo do cliente"),
 *     @OA\Property(property="email", type="string", format="email", example="joao.silva@example.com", description="E-mail do cliente"),
 *     @OA\Property(property="phone", type="string", example="11999999999", description="Telefone do cliente"),
 *     @OA\Property(property="birthdate", type="string", format="date", example="1990-01-15", description="Data de nascimento"),
 *     @OA\Property(property="cpf", type="string", example="123.456.789-00", description="CPF do cliente"),
 *     @OA\Property(property="notes", type="string", example="Paciente com histórico de alergia", description="Observações sobre o cliente"),
 *     @OA\Property(property="image", type="string", nullable=true, example="customers/photo.jpg", description="Caminho da foto do cliente")
 * )
 *
 * @OA\Schema(
 *     schema="Room",
 *     type="object",
 *     title="Sala",
 *     description="Modelo de sala/consultório",
 *     @OA\Property(property="id", type="integer", example=1, description="ID da sala"),
 *     @OA\Property(property="name", type="string", example="Consultório 1", description="Nome da sala"),
 *     @OA\Property(property="location", type="string", example="Andar 2 - Sala 201", description="Localização da sala"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de criação"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de atualização")
 * )
 *
 * @OA\Schema(
 *     schema="Service",
 *     type="object",
 *     title="Serviço",
 *     description="Modelo de serviço oferecido pela clínica",
 *     @OA\Property(property="id", type="integer", example=1, description="ID do serviço"),
 *     @OA\Property(property="name", type="string", example="Consulta Clínica Geral", description="Nome do serviço"),
 *     @OA\Property(property="description", type="string", example="Consulta médica geral com duração de 30 minutos", description="Descrição do serviço"),
 *     @OA\Property(property="price", type="string", example="150.00", description="Preço do serviço formatado"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de criação"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de atualização")
 * )
 *
 * @OA\Schema(
 *     schema="Role",
 *     type="object",
 *     title="Papel/Função",
 *     description="Papel do usuário no sistema",
 *     @OA\Property(property="id", type="integer", example=1, description="ID do papel"),
 *     @OA\Property(property="name", type="string", example="Administrador", description="Nome do papel"),
 *     @OA\Property(property="type", type="string", enum={"ADMIN", "DOCTOR", "RECEPTIONIST", "FINANCE"}, example="ADMIN", description="Tipo de papel")
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="Usuário",
 *     description="Modelo de usuário do sistema",
 *     @OA\Property(property="id", type="integer", example=1, description="ID do usuário"),
 *     @OA\Property(property="id_role", type="integer", example=1, description="ID do papel do usuário"),
 *     @OA\Property(property="name", type="string", example="Dr. Carlos Souza", description="Nome completo do usuário"),
 *     @OA\Property(property="email", type="string", format="email", example="carlos.souza@example.com", description="E-mail do usuário"),
 *     @OA\Property(property="phone", type="string", example="11988888888", description="Telefone do usuário"),
 *     @OA\Property(property="crm", type="string", nullable=true, example="CRM/SP 123456", description="Número do CRM (para médicos)"),
 *     @OA\Property(property="image", type="string", nullable=true, example="users/photo.jpg", description="Caminho da foto do usuário"),
 *     @OA\Property(property="image_url", type="string", nullable=true, example="https://example.com/storage/users/photo.jpg", description="URL completa da foto"),
 *     @OA\Property(property="is_owner", type="boolean", example=false, description="Indica se o usuário é proprietário da empresa"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de criação"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de atualização"),
 *     @OA\Property(property="role", ref="#/components/schemas/Role", description="Papel do usuário")
 * )
 *
 * @OA\Schema(
 *     schema="Appointment",
 *     type="object",
 *     title="Agendamento",
 *     description="Modelo de agendamento de consulta",
 *     @OA\Property(property="id", type="integer", example=1, description="ID do agendamento"),
 *     @OA\Property(property="id_user", type="integer", example=1, description="ID do profissional"),
 *     @OA\Property(property="id_customer", type="integer", example=1, description="ID do cliente/paciente"),
 *     @OA\Property(property="id_room", type="integer", example=1, description="ID da sala"),
 *     @OA\Property(property="id_service", type="integer", example=1, description="ID do serviço"),
 *     @OA\Property(property="date", type="string", format="date", example="2025-01-20", description="Data do agendamento"),
 *     @OA\Property(property="start_time", type="string", format="time", example="14:00:00", description="Horário de início"),
 *     @OA\Property(property="end_time", type="string", format="time", example="15:00:00", description="Horário de término"),
 *     @OA\Property(property="price", type="string", example="150.00", description="Preço do serviço formatado"),
 *     @OA\Property(property="status", type="string", enum={"SCHEDULED", "IN_PROGRESS", "COMPLETED", "CANCELLED"}, example="SCHEDULED", description="Status do agendamento"),
 *     @OA\Property(property="notes", type="string", nullable=true, example="Primeira consulta do paciente", description="Observações sobre o agendamento"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de criação"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de atualização"),
 *     @OA\Property(property="customer", ref="#/components/schemas/Customer", description="Dados do cliente"),
 *     @OA\Property(property="user", ref="#/components/schemas/User", description="Dados do profissional"),
 *     @OA\Property(property="room", ref="#/components/schemas/Room", description="Dados da sala"),
 *     @OA\Property(property="service", ref="#/components/schemas/Service", description="Dados do serviço")
 * )
 *
 * @OA\Schema(
 *     schema="MedicalRecord",
 *     type="object",
 *     title="Prontuário Médico",
 *     description="Modelo de prontuário médico do paciente",
 *     @OA\Property(property="id", type="integer", example=1, description="ID do prontuário"),
 *     @OA\Property(property="id_user", type="integer", example=1, description="ID do profissional"),
 *     @OA\Property(property="id_customer", type="integer", example=1, description="ID do cliente/paciente"),
 *     @OA\Property(property="id_appointment", type="integer", nullable=true, example=1, description="ID do agendamento relacionado"),
 *     @OA\Property(property="chief_complaint", type="string", example="Dor de cabeça persistente", description="Queixa principal do paciente"),
 *     @OA\Property(property="physical_exam", type="string", example="Paciente consciente e orientado", description="Exame físico realizado"),
 *     @OA\Property(property="diagnosis", type="string", example="Enxaqueca crônica", description="Diagnóstico médico"),
 *     @OA\Property(property="treatment_plan", type="string", example="Prescrição de analgésicos e acompanhamento", description="Plano de tratamento"),
 *     @OA\Property(property="prescriptions", type="string", nullable=true, example="Paracetamol 500mg - 1 comprimido a cada 8h", description="Prescrições médicas"),
 *     @OA\Property(property="observations", type="string", nullable=true, example="Paciente relatou melhora após medicação", description="Observações adicionais"),
 *     @OA\Property(property="follow_up_date", type="string", format="date", nullable=true, example="2025-02-20", description="Data de retorno"),
 *     @OA\Property(property="medical_history", type="string", nullable=true, example="Histórico de hipertensão", description="Histórico médico do paciente"),
 *     @OA\Property(property="allergies", type="string", nullable=true, example="Alergia a dipirona", description="Alergias conhecidas"),
 *     @OA\Property(property="medications", type="string", nullable=true, example="Losartana 50mg diário", description="Medicações em uso"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de criação"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de atualização"),
 *     @OA\Property(property="customer", ref="#/components/schemas/Customer", description="Dados do cliente"),
 *     @OA\Property(property="user", ref="#/components/schemas/User", description="Dados do profissional"),
 *     @OA\Property(property="appointment", ref="#/components/schemas/Appointment", description="Dados do agendamento")
 * )
 *
 * @OA\Schema(
 *     schema="MarketingCampaign",
 *     type="object",
 *     title="Campanha de Marketing",
 *     description="Modelo de campanha de marketing via WhatsApp",
 *     @OA\Property(property="id", type="integer", example=1, description="ID da campanha"),
 *     @OA\Property(property="name", type="string", example="Campanha Janeiro 2025", description="Nome da campanha"),
 *     @OA\Property(property="message", type="string", example="Olá! Aproveite nossos descontos especiais", description="Mensagem da campanha"),
 *     @OA\Property(property="media_type", type="string", nullable=true, enum={"image", "video", "document"}, example="image", description="Tipo de mídia anexada"),
 *     @OA\Property(property="media_url", type="string", nullable=true, example="https://example.com/media/image.jpg", description="URL da mídia"),
 *     @OA\Property(property="media_filename", type="string", nullable=true, example="promocao.jpg", description="Nome do arquivo de mídia"),
 *     @OA\Property(property="local_media_path", type="string", nullable=true, example="campaigns/media/file.jpg", description="Caminho local da mídia"),
 *     @OA\Property(property="status", type="string", enum={"draft", "scheduled", "sending", "sent", "cancelled"}, example="scheduled", description="Status da campanha"),
 *     @OA\Property(property="target_audience", type="string", enum={"all", "filtered"}, example="all", description="Público-alvo"),
 *     @OA\Property(property="target_filters", type="object", nullable=true, description="Filtros aplicados ao público-alvo"),
 *     @OA\Property(property="scheduled_at", type="string", format="date-time", nullable=true, example="2025-01-20T09:00:00Z", description="Data/hora agendada para envio"),
 *     @OA\Property(property="sent_at", type="string", format="date-time", nullable=true, example="2025-01-20T09:05:00Z", description="Data/hora do envio"),
 *     @OA\Property(property="total_recipients", type="integer", example=150, description="Total de destinatários"),
 *     @OA\Property(property="sent_count", type="integer", example=148, description="Quantidade de envios bem-sucedidos"),
 *     @OA\Property(property="failed_count", type="integer", example=2, description="Quantidade de envios falhados"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de criação"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de atualização")
 * )
 *
 * @OA\Schema(
 *     schema="FinancialAccount",
 *     type="object",
 *     title="Conta Financeira",
 *     description="Modelo de conta financeira (banco, caixa, etc.)",
 *     @OA\Property(property="id", type="integer", example=1, description="ID da conta"),
 *     @OA\Property(property="name", type="string", example="Caixa Principal", description="Nome da conta"),
 *     @OA\Property(property="type", type="string", enum={"BANK", "CASH", "CREDIT_CARD"}, example="BANK", description="Tipo de conta"),
 *     @OA\Property(property="bank_name", type="string", nullable=true, example="Banco do Brasil", description="Nome do banco"),
 *     @OA\Property(property="account_number", type="string", nullable=true, example="12345-6", description="Número da conta"),
 *     @OA\Property(property="initial_balance", type="string", example="1000.00", description="Saldo inicial formatado"),
 *     @OA\Property(property="current_balance", type="string", example="2500.50", description="Saldo atual formatado"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Indica se a conta está ativa"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Conta para recebimentos", description="Descrição da conta"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de criação"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de atualização")
 * )
 *
 * @OA\Schema(
 *     schema="FinancialCategory",
 *     type="object",
 *     title="Categoria Financeira",
 *     description="Modelo de categoria para transações financeiras",
 *     @OA\Property(property="id", type="integer", example=1, description="ID da categoria"),
 *     @OA\Property(property="name", type="string", example="Consultas", description="Nome da categoria"),
 *     @OA\Property(property="type", type="string", enum={"INCOME", "EXPENSE"}, example="INCOME", description="Tipo de categoria"),
 *     @OA\Property(property="color", type="string", nullable=true, example="#4CAF50", description="Cor da categoria (hexadecimal)"),
 *     @OA\Property(property="icon", type="string", nullable=true, example="medical-services", description="Ícone da categoria"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Indica se a categoria está ativa"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Receitas de consultas médicas", description="Descrição da categoria"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de criação"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de atualização")
 * )
 *
 * @OA\Schema(
 *     schema="FinancialTransaction",
 *     type="object",
 *     title="Transação Financeira",
 *     description="Modelo de transação financeira (receita, despesa, transferência)",
 *     @OA\Property(property="id", type="integer", example=1, description="ID da transação"),
 *     @OA\Property(property="type", type="string", enum={"INCOME", "EXPENSE", "TRANSFER"}, example="INCOME", description="Tipo de transação"),
 *     @OA\Property(property="amount", type="string", example="150.00", description="Valor da transação formatado"),
 *     @OA\Property(property="description", type="string", example="Pagamento de consulta", description="Descrição da transação"),
 *     @OA\Property(property="transaction_date", type="string", format="date", example="2025-01-15", description="Data da transação"),
 *     @OA\Property(property="due_date", type="string", format="date", nullable=true, example="2025-01-20", description="Data de vencimento"),
 *     @OA\Property(property="status", type="string", enum={"PENDING", "PAID", "OVERDUE", "CANCELLED"}, example="PAID", description="Status da transação"),
 *     @OA\Property(property="payment_method", type="string", nullable=true, enum={"CASH", "CREDIT_CARD", "DEBIT_CARD", "PIX", "BANK_TRANSFER", "CHECK"}, example="PIX", description="Método de pagamento"),
 *     @OA\Property(property="document_number", type="string", nullable=true, example="NF-12345", description="Número do documento"),
 *     @OA\Property(property="notes", type="string", nullable=true, example="Pagamento recebido em espécie", description="Observações"),
 *     @OA\Property(property="attachments", type="array", nullable=true, @OA\Items(type="string"), example={"comprovante.pdf", "nota.jpg"}, description="Anexos da transação"),
 *     @OA\Property(property="id_financial_account", type="integer", example=1, description="ID da conta financeira"),
 *     @OA\Property(property="id_financial_category", type="integer", example=1, description="ID da categoria financeira"),
 *     @OA\Property(property="id_customer", type="integer", nullable=true, example=1, description="ID do cliente relacionado"),
 *     @OA\Property(property="id_appointment", type="integer", nullable=true, example=1, description="ID do agendamento relacionado"),
 *     @OA\Property(property="id_transfer_account", type="integer", nullable=true, example=2, description="ID da conta de destino (para transferências)"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de criação"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de atualização"),
 *     @OA\Property(property="account", ref="#/components/schemas/FinancialAccount", description="Dados da conta"),
 *     @OA\Property(property="category", ref="#/components/schemas/FinancialCategory", description="Dados da categoria"),
 *     @OA\Property(property="customer", ref="#/components/schemas/Customer", description="Dados do cliente"),
 *     @OA\Property(property="appointment", ref="#/components/schemas/Appointment", description="Dados do agendamento"),
 *     @OA\Property(property="transfer_account", ref="#/components/schemas/FinancialAccount", description="Dados da conta de destino")
 * )
 *
 * @OA\Schema(
 *     schema="InventoryCategory",
 *     type="object",
 *     title="Categoria de Produto",
 *     description="Categoria de produto do estoque",
 *     @OA\Property(property="id", type="integer", example=1, description="ID da categoria"),
 *     @OA\Property(property="name", type="string", example="Medicamentos", description="Nome da categoria")
 * )
 *
 * @OA\Schema(
 *     schema="InventorySupplier",
 *     type="object",
 *     title="Fornecedor",
 *     description="Fornecedor de produtos",
 *     @OA\Property(property="id", type="integer", example=1, description="ID do fornecedor"),
 *     @OA\Property(property="name", type="string", example="Farmacêutica ABC", description="Nome do fornecedor")
 * )
 *
 * @OA\Schema(
 *     schema="InventoryProduct",
 *     type="object",
 *     title="Produto do Estoque",
 *     description="Modelo de produto do estoque/inventário",
 *     @OA\Property(property="id", type="integer", example=1, description="ID do produto"),
 *     @OA\Property(property="id_category", type="integer", nullable=true, example=1, description="ID da categoria"),
 *     @OA\Property(property="id_supplier", type="integer", nullable=true, example=1, description="ID do fornecedor"),
 *     @OA\Property(property="name", type="string", example="Paracetamol 500mg", description="Nome do produto"),
 *     @OA\Property(property="code", type="string", nullable=true, example="MED-001", description="Código do produto"),
 *     @OA\Property(property="barcode", type="string", nullable=true, example="7891234567890", description="Código de barras"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Analgésico e antitérmico", description="Descrição do produto"),
 *     @OA\Property(property="unit", type="string", example="Caixa", description="Unidade de medida"),
 *     @OA\Property(property="current_stock", type="string", example="50.00", description="Estoque atual formatado"),
 *     @OA\Property(property="minimum_stock", type="string", example="10.00", description="Estoque mínimo formatado"),
 *     @OA\Property(property="maximum_stock", type="string", nullable=true, example="100.00", description="Estoque máximo formatado"),
 *     @OA\Property(property="cost_price", type="string", nullable=true, example="5.50", description="Preço de custo formatado"),
 *     @OA\Property(property="sale_price", type="string", nullable=true, example="12.00", description="Preço de venda formatado"),
 *     @OA\Property(property="expiry_date", type="string", format="date", nullable=true, example="2026-12-31", description="Data de validade"),
 *     @OA\Property(property="batch_number", type="string", nullable=true, example="LOTE-2025-01", description="Número do lote"),
 *     @OA\Property(property="storage_location", type="string", nullable=true, example="Prateleira A3", description="Local de armazenamento"),
 *     @OA\Property(property="requires_prescription", type="boolean", example=false, description="Indica se requer receita médica"),
 *     @OA\Property(property="controlled_substance", type="boolean", example=false, description="Indica se é substância controlada"),
 *     @OA\Property(property="active", type="boolean", example=true, description="Indica se o produto está ativo"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de criação"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-15T10:30:00Z", description="Data de atualização"),
 *     @OA\Property(property="category", ref="#/components/schemas/InventoryCategory", description="Dados da categoria"),
 *     @OA\Property(property="supplier", ref="#/components/schemas/InventorySupplier", description="Dados do fornecedor")
 * )
 *
 * @OA\Schema(
 *     schema="MedicalCertificate",
 *     type="object",
 *     title="Atestado Médico",
 *     description="Modelo de atestado médico digital com verificação",
 *     @OA\Property(property="id", type="integer", example=1, description="ID do atestado"),
 *     @OA\Property(property="id_user", type="integer", example=1, description="ID do médico"),
 *     @OA\Property(property="id_customer", type="integer", example=1, description="ID do paciente"),
 *     @OA\Property(property="id_appointment", type="integer", nullable=true, example=1, description="ID do agendamento relacionado"),
 *     @OA\Property(property="content", type="string", example="Atesto para os devidos fins que o paciente necessita de repouso médico", description="Conteúdo do atestado"),
 *     @OA\Property(property="days_off", type="integer", example=3, description="Número de dias de afastamento"),
 *     @OA\Property(property="issue_date", type="string", format="date", example="2025-01-20", description="Data de emissão"),
 *     @OA\Property(property="valid_until", type="string", format="date", example="2025-01-25", description="Data de validade"),
 *     @OA\Property(property="digital_signature", type="string", example="a1b2c3d4e5f6...", description="Assinatura digital do médico"),
 *     @OA\Property(property="validation_hash", type="string", example="550e8400-e29b-41d4-a716-446655440000", description="Hash único para verificação"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-20T10:30:00Z", description="Data de criação"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-20T10:30:00Z", description="Data de atualização"),
 *     @OA\Property(property="customer", ref="#/components/schemas/Customer", description="Dados do paciente"),
 *     @OA\Property(property="user", ref="#/components/schemas/User", description="Dados do médico")
 * )
 */
trait SwaggerSchemas
{
    // Trait vazia, apenas para armazenar as definições de schemas do Swagger
}
