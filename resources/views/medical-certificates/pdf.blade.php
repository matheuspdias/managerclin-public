<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Atestado Médico - {{ $certificate->id }}</title>
    <style>
        @page {
            margin: 2cm;
            size: A4;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .document-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px 0;
            border-bottom: 3px solid #1e40af;
            position: relative;
        }

        .clinic-info {
            margin-bottom: 15px;
        }

        .clinic-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }

        .clinic-details {
            font-size: 11px;
            color: #666;
            line-height: 1.4;
        }

        .document-title {
            font-size: 28px;
            font-weight: bold;
            color: #1e40af;
            margin: 20px 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .document-subtitle {
            font-size: 12px;
            color: #666;
            font-style: italic;
        }

        .content-section {
            margin: 40px 0;
            padding: 25px;
            background-color: #f8fafc;
            border-left: 5px solid #1e40af;
            border-radius: 8px;
        }

        .attest-text {
            font-size: 14px;
            line-height: 1.8;
            text-align: justify;
            margin-bottom: 20px;
        }

        .patient-info {
            background-color: #fff;
            padding: 20px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            margin: 20px 0;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin: 20px 0;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            color: #374151;
            padding: 8px 15px 8px 0;
            width: 30%;
        }

        .info-value {
            display: table-cell;
            color: #1f2937;
            padding: 8px 0;
        }

        .medical-content {
            font-size: 13px;
            line-height: 1.7;
            text-align: justify;
            margin: 25px 0;
            padding: 20px;
            background-color: #fff;
            border-radius: 6px;
            border: 1px solid #d1d5db;
        }

        .signature-section {
            margin-top: 60px;
            page-break-inside: avoid;
        }

        .signature-box {
            border: 2px solid #1e40af;
            border-radius: 10px;
            padding: 25px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            text-align: center;
        }

        .signature-line {
            border-top: 2px solid #374151;
            width: 300px;
            margin: 30px auto 20px auto;
        }

        .doctor-name {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
            margin: 10px 0;
        }

        .doctor-crm {
            font-size: 14px;
            color: #374151;
            font-weight: 600;
        }

        .digital-signature {
            margin-top: 15px;
            font-size: 10px;
            color: #6b7280;
            padding: 10px;
            background-color: #f3f4f6;
            border-radius: 4px;
        }

        .verification-section {
            margin-top: 40px;
            padding: 25px;
            background: linear-gradient(135deg, #f0f9ff 0%, #e6f7ff 100%);
            border: 2px solid #3b82f6;
            border-radius: 12px;
            page-break-inside: avoid;
        }

        .verification-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .verification-title {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .verification-subtitle {
            font-size: 11px;
            color: #475569;
            font-style: italic;
        }

        .verification-content {
            display: table;
            width: 100%;
        }

        .qr-section {
            display: table-cell;
            width: 140px;
            text-align: center;
            vertical-align: top;
            padding-right: 20px;
        }

        .qr-container {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            border: 2px solid #dbeafe;
            display: inline-block;
        }

        .qr-label {
            font-size: 9px;
            color: #6b7280;
            margin-top: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .url-section {
            display: table-cell;
            vertical-align: top;
        }

        .verification-url {
            background-color: white;
            padding: 15px;
            border: 1px dashed #3b82f6;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .url-label {
            font-size: 11px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .url-text {
            font-size: 10px;
            color: #374151;
            word-break: break-all;
            line-height: 1.4;
            font-family: monospace;
        }

        .security-warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 12px;
            font-size: 9px;
            color: #92400e;
            text-align: center;
            margin-top: 15px;
        }

        .document-footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
            line-height: 1.4;
        }

        .validity-badge {
            background-color: #10b981;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
            text-transform: uppercase;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(30, 64, 175, 0.05);
            font-weight: bold;
            z-index: -1;
            pointer-events: none;
        }
    </style>
</head>

<body>
    <!-- Watermark de segurança -->
    <div class="watermark">ATESTADO MÉDICO VÁLIDO</div>

    <!-- Cabeçalho do documento -->
    <div class="document-header">
        <div class="clinic-info">
            @if($certificate->user->company)
                <div class="clinic-name">{{ $certificate->user->company->name }}</div>
                <div class="clinic-details">
                    Clínica Médica | Sistema de Gestão de Saúde<br>
                    Documento emitido digitalmente
                </div>
            @endif
        </div>

        <div class="document-title">Atestado Médico</div>
        <div class="document-subtitle">Documento oficial com validade jurídica</div>

        <div class="validity-badge">Documento Válido</div>
    </div>

    <!-- Informações do paciente -->
    <div class="patient-info">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Paciente:</div>
                <div class="info-value">{{ $certificate->customer->name }}</div>
            </div>
            @if($certificate->customer->email)
            <div class="info-row">
                <div class="info-label">E-mail:</div>
                <div class="info-value">{{ $certificate->customer->email }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Data de emissão:</div>
                <div class="info-value">{{ $certificate->issue_date->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Válido até:</div>
                <div class="info-value">{{ $certificate->valid_until->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Período de afastamento:</div>
                <div class="info-value">{{ $certificate->days_off }} {{ $certificate->days_off == 1 ? 'dia' : 'dias' }}</div>
            </div>
        </div>
    </div>

    <!-- Conteúdo médico -->
    <div class="content-section">
        <div class="attest-text">
            <strong>Atesto para os devidos fins</strong> que o(a) paciente acima identificado(a) esteve sob meus cuidados médicos profissionais.
        </div>

        <div class="medical-content">
            {!! nl2br(e($certificate->content)) !!}
        </div>

        <div class="attest-text">
            Este atestado é emitido em conformidade com o Código de Ética Médica e legislação vigente, sendo válido para todos os fins legais.
        </div>
    </div>

    <!-- Seção de assinatura -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="doctor-name">{{ $certificate->user->name }}</div>
            <div class="doctor-crm">
                @if($certificate->user->crm)
                    CRM: {{ $certificate->user->crm }}
                @else
                    Registro Profissional: Não informado
                @endif
            </div>

            <div class="digital-signature">
                <strong>Assinatura Digital:</strong> {{ substr($certificate->digital_signature, 0, 20) }}...<br>
                <strong>Emitido em:</strong> {{ $issueDate ?? now()->format('d/m/Y \à\s H:i') }}
            </div>
        </div>
    </div>

    <!-- Seção de verificação -->
    <div class="verification-section">
        <div class="verification-header">
            <div class="verification-title">🔒 Verificação de Autenticidade</div>
            <div class="verification-subtitle">Este documento pode ser validado online</div>
        </div>

        <div class="verification-content">
            <div class="qr-section">
                <div class="qr-container">
                    @if(isset($qrCode))
                        <img src="data:image/png;base64,{{ $qrCode }}"
                             alt="QR Code de verificação"
                             style="width: 100px; height: 100px;">
                    @endif
                    <div class="qr-label">Escaneie o QR Code</div>
                </div>
            </div>

            <div class="url-section">
                <div class="verification-url">
                    <div class="url-label">📱 Verificação por URL:</div>
                    <div class="url-text">
                        {{ $verificationUrl ?? route('medical-certificates.verify', $certificate->validation_hash) }}
                    </div>
                </div>

                <div class="verification-url">
                    <div class="url-label">🔐 Código de Validação:</div>
                    <div class="url-text">{{ $certificate->validation_hash }}</div>
                </div>
            </div>
        </div>

        <div class="security-warning">
            ⚠️ IMPORTANTE: Verifique sempre a autenticidade deste documento através dos meios de verificação acima.
            Documentos médicos falsos constituem crime.
        </div>
    </div>

    <!-- Rodapé do documento -->
    <div class="document-footer">
        Documento emitido eletronicamente por {{ config('app.name') }} em {{ now()->format('d/m/Y H:i:s') }}<br>
        Este atestado médico possui validade jurídica conforme Lei Federal nº 14.063/2020 (Assinaturas Eletrônicas)<br>
        Código do documento: {{ $certificate->id }} | Hash de validação: {{ substr($certificate->validation_hash, 0, 16) }}...
    </div>
</body>

</html>
