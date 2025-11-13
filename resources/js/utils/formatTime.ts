export function formatHour(time: string) {
    return time?.slice(0, 5); // "HH:mm"
}

export function formatToTimeInput(datetime: string): string {
    // Se vier algo como "14:00:00" ou "2025-07-21T14:00:00.000000Z"
    const date = new Date(`1970-01-01T${datetime}`);
    return date.toTimeString().slice(0, 5); // "HH:mm"
}
