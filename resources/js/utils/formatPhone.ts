export function formatPhoneBR(value: string): string {
    // Remove tudo que não é dígito
    const digits = value.replace(/\D/g, '');

    if (digits.length === 0) return '';

    if (digits.length <= 2) {
        return `(${digits}`;
    }

    if (digits.length <= 7) {
        return `(${digits.slice(0, 2)}) ${digits.slice(2)}`;
    }

    if (digits.length <= 11) {
        return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7)}`;
    }

    return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7, 11)}`;
}

// Função para remover tudo que não é dígito para enviar ao backend
export function onlyDigits(value: string): string {
    return value.replace(/\D/g, '');
}
