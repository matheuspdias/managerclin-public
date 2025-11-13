interface Error403Props {
    message?: string;
}

export default function Error403({ message }: Error403Props) {
    return (
        <div className="flex min-h-screen flex-col items-center justify-center text-center">
            <h1 className="text-6xl font-bold text-red-500">403</h1>
            <p className="mt-4 text-lg text-gray-600">
                {message || 'Você não tem permissão para acessar esta página.'}
            </p>
            <a href="/dashboard" className="mt-6 rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600">
                Voltar para a página inicial
            </a>
        </div>
    );
}
