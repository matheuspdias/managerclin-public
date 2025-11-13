import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/react';
import { AlertCircle, CheckCircle, Clock, MessageCircle, Minimize2, Send, Sparkles, Trash2, User, X } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';

interface Message {
    text: string;
    isUser: boolean;
    timestamp: Date;
    status?: 'sending' | 'sent' | 'error';
}

interface ChatState {
    isOpen: boolean;
    isMinimized: boolean;
    messages: Message[];
}

const CHAT_STORAGE_KEY = 'livia-chat-state';
const CHAT_EXPIRY_HOURS = 24; // Conversas expiram em 24 horas

export default function ChatFloatingButton() {
    const [isOpen, setIsOpen] = useState(false);
    const [isMinimized, setIsMinimized] = useState(false);
    const [messages, setMessages] = useState<Message[]>([]);
    const [isLoading, setIsLoading] = useState(false);
    const [connectionStatus, setConnectionStatus] = useState<'connected' | 'disconnected' | 'error'>('connected');
    const messagesEndRef = useRef<HTMLDivElement>(null);
    const controllerRef = useRef<AbortController | null>(null);
    const textareaRef = useRef<HTMLTextAreaElement>(null);
    const [isInitialized, setIsInitialized] = useState(false);

    const { data, setData, processing, reset } = useForm({
        mensagem: '',
    });

    // Funções de persistência
    const getDefaultChatState = useCallback(
        (): ChatState => ({
            isOpen: false,
            isMinimized: false,
            messages: [],
        }),
        [],
    );

    const loadChatState = useCallback((): ChatState => {
        try {
            const saved = localStorage.getItem(CHAT_STORAGE_KEY);
            if (!saved) return getDefaultChatState();

            const parsed = JSON.parse(saved);

            // Verificar se não expirou
            const expiryTime = CHAT_EXPIRY_HOURS * 60 * 60 * 1000;
            if (parsed.timestamp && Date.now() - parsed.timestamp > expiryTime) {
                localStorage.removeItem(CHAT_STORAGE_KEY);
                return getDefaultChatState();
            }

            // Converter timestamps de volta para Date
            const messages = (parsed.messages || []).map(
                (msg: { text: string; isUser: boolean; timestamp: string; status?: 'sending' | 'sent' | 'error' }) => ({
                    ...msg,
                    timestamp: new Date(msg.timestamp),
                }),
            );

            return {
                isOpen: parsed.isOpen || false,
                isMinimized: parsed.isMinimized || false,
                messages: messages,
            };
        } catch (error) {
            console.warn('Erro ao carregar estado do chat:', error);
            return getDefaultChatState();
        }
    }, [getDefaultChatState]);

    const saveChatState = useCallback(
        (state: Partial<ChatState>) => {
            try {
                const currentState = loadChatState();
                const newState: ChatState = {
                    ...currentState,
                    ...state,
                };
                const stateWithTimestamp = {
                    ...newState,
                    timestamp: Date.now(),
                    messages: newState.messages.map((msg) => ({
                        ...msg,
                        timestamp: msg.timestamp instanceof Date ? msg.timestamp.toISOString() : msg.timestamp,
                    })),
                };
                localStorage.setItem(CHAT_STORAGE_KEY, JSON.stringify(stateWithTimestamp));
            } catch (error) {
                console.warn('Erro ao salvar estado do chat:', error);
            }
        },
        [loadChatState],
    );

    const getWelcomeMessage = useCallback(
        (): Message => ({
            text: '👋 Olá! Sou a **Lívia**, sua assistente médica virtual.\n\nPosso ajudar com:\n• 📋 Consulta de prontuários\n• 📅 Informações sobre agendamentos\n• 👥 Lista de pacientes\n• 💡 Dúvidas gerais\n\nComo posso ajudá-lo(a) hoje?',
            isUser: false,
            timestamp: new Date(),
            status: 'sent',
        }),
        [],
    );

    const clearChatHistory = useCallback(() => {
        try {
            const welcomeMessage = getWelcomeMessage();
            setMessages([welcomeMessage]);
            localStorage.removeItem(CHAT_STORAGE_KEY);
            toast.success('Histórico do chat limpo com sucesso!');
        } catch (error) {
            console.warn('Erro ao limpar estado do chat:', error);
            toast.error('Erro ao limpar histórico do chat');
        }
    }, [getWelcomeMessage]);

    // Carregar estado inicial
    useEffect(() => {
        if (!isInitialized) {
            const savedState = loadChatState();
            setIsOpen(savedState.isOpen);
            setIsMinimized(savedState.isMinimized);

            if (savedState.messages.length > 0) {
                setMessages(savedState.messages);
            }

            setIsInitialized(true);
        }
    }, [isInitialized, loadChatState]);

    // Salvar estado quando mudanças importantes acontecem
    useEffect(() => {
        if (isInitialized) {
            saveChatState({ isOpen, isMinimized, messages });
        }
    }, [isOpen, isMinimized, messages, isInitialized, saveChatState]);

    const toggleChat = () => {
        const newIsOpen = !isOpen;
        setIsOpen(newIsOpen);
        setIsMinimized(false);

        // Se está abrindo o chat e não há mensagens, adicionar mensagem de boas-vindas
        if (newIsOpen && messages.length === 0) {
            const welcomeMessage = getWelcomeMessage();
            setMessages([welcomeMessage]);
        }
    };

    const minimizeChat = () => {
        setIsMinimized(!isMinimized);
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!data.mensagem.trim()) return;

        const userMessage: Message = {
            text: data.mensagem,
            isUser: true,
            timestamp: new Date(),
            status: 'sent',
        };

        const messageText = data.mensagem;
        setMessages((prev) => [...prev, userMessage]);
        reset();
        setIsLoading(true);
        setConnectionStatus('connected');

        // Cancela qualquer requisição anterior
        if (controllerRef.current) {
            controllerRef.current.abort();
        }

        // Cria novo controller para a requisição
        controllerRef.current = new AbortController();

        try {
            const history = messages.map((message) => ({
                role: message.isUser ? 'user' : 'assistant',
                content: message.text,
            }));

            const response = await fetch(route('chat.stream'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'text/event-stream',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ mensagem: messageText, history }),
                signal: controllerRef.current.signal,
            });

            if (!response.ok) {
                if (response.status === 402) {
                    throw new Error('Créditos insuficientes. Compre mais créditos para continuar usando o assistente.');
                } else if (response.status === 400) {
                    throw new Error('Dados inválidos. Verifique sua mensagem e tente novamente.');
                } else {
                    throw new Error('Erro no servidor. Tente novamente.');
                }
            }

            const reader = response.body?.getReader();
            if (!reader) throw new Error('Não foi possível ler o stream');

            const botMessage: Message = {
                text: '',
                isUser: false,
                timestamp: new Date(),
                status: 'sending',
            };
            setMessages((prev) => [...prev, botMessage]);

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                const textChunk = new TextDecoder().decode(value);
                botMessage.text += textChunk;
                botMessage.status = 'sent';

                // Atualiza a última mensagem (do bot) com o novo chunk
                setMessages((prev) => {
                    const newMessages = [...prev];
                    newMessages[newMessages.length - 1] = { ...botMessage };
                    return newMessages;
                });
            }
        } catch (error) {
            if (error instanceof Error && error.name !== 'AbortError') {
                setConnectionStatus('error');
                const errorMessage: Message = {
                    text: error.message || 'Ocorreu um erro ao processar sua mensagem.',
                    isUser: false,
                    timestamp: new Date(),
                    status: 'error',
                };
                setMessages((prev) => [...prev, errorMessage]);

                if (error.message.includes('Créditos insuficientes')) {
                    toast.error('Créditos insuficientes! Compre mais créditos para continuar.');
                }
            }
        } finally {
            setIsLoading(false);
            controllerRef.current = null;
        }
    };

    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    useEffect(() => {
        return () => {
            if (controllerRef.current) {
                controllerRef.current.abort();
            }
        };
    }, []);

    // Auto-resize textarea
    useEffect(() => {
        if (textareaRef.current) {
            textareaRef.current.style.height = 'auto';
            textareaRef.current.style.height = `${Math.min(textareaRef.current.scrollHeight, 120)}px`;
        }
    }, [data.mensagem]);

    const formatTime = (date: Date) => {
        return date.toLocaleTimeString('pt-BR', {
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const renderMessage = (message: Message, index: number) => {
        const isUser = message.isUser;
        return (
            <div key={index} className={`flex ${isUser ? 'justify-end' : 'justify-start'} mb-4`}>
                <div className={`flex ${isUser ? 'flex-row-reverse' : 'flex-row'} max-w-[85%] items-end space-x-2`}>
                    {/* Avatar */}
                    <div
                        className={`flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full ${
                            isUser ? 'bg-blue-500 text-white' : 'bg-gradient-to-br from-purple-500 to-pink-500 text-white'
                        }`}
                    >
                        {isUser ? <User className="h-4 w-4" /> : <Sparkles className="h-4 w-4" />}
                    </div>

                    {/* Message bubble */}
                    <div className={`space-y-1 ${isUser ? 'mr-2' : 'ml-2'}`}>
                        <div
                            className={`rounded-2xl px-4 py-2 shadow-sm ${
                                isUser
                                    ? 'rounded-br-md bg-blue-500 text-white'
                                    : message.status === 'error'
                                      ? 'rounded-bl-md border border-red-200 bg-red-50 text-red-800'
                                      : 'rounded-bl-md bg-gray-100 text-gray-800'
                            }`}
                        >
                            <div className="text-sm break-words whitespace-pre-wrap">
                                {message.text.split('\n').map((line, i) => {
                                    if (line.startsWith('• ')) {
                                        return (
                                            <div key={i} className="ml-2">
                                                {line}
                                            </div>
                                        );
                                    }
                                    if (line.includes('**') && line.includes('**')) {
                                        const parts = line.split('**');
                                        return <div key={i}>{parts.map((part, j) => (j % 2 === 1 ? <strong key={j}>{part}</strong> : part))}</div>;
                                    }
                                    return <div key={i}>{line}</div>;
                                })}
                            </div>
                        </div>

                        {/* Status and timestamp */}
                        <div className={`flex items-center space-x-1 text-xs text-gray-500 ${isUser ? 'justify-end' : 'justify-start'}`}>
                            <span>{formatTime(message.timestamp)}</span>
                            {isUser && (
                                <>
                                    {message.status === 'sending' && <Clock className="h-3 w-3" />}
                                    {message.status === 'sent' && <CheckCircle className="h-3 w-3" />}
                                    {message.status === 'error' && <AlertCircle className="h-3 w-3 text-red-500" />}
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        );
    };

    return (
        <>
            {/* Chat Panel */}
            {isOpen && (
                <div
                    className={`fixed top-0 right-0 bottom-0 z-50 w-96 max-w-full transition-all duration-300 ${
                        isMinimized ? 'translate-y-full transform' : ''
                    }`}
                >
                    <Card className="flex h-full w-full flex-col rounded-none border-l border-gray-200 bg-white shadow-2xl">
                        {/* Header */}
                        <CardHeader className="bg-gradient-to-r from-purple-600 to-pink-600 p-4 text-white">
                            <div className="flex items-center justify-between">
                                <CardTitle className="flex items-center gap-3 text-lg">
                                    <div className="flex h-8 w-8 items-center justify-center rounded-full bg-white/20">
                                        <Sparkles className="h-4 w-4" />
                                    </div>
                                    <div>
                                        <div className="font-semibold">Lívia IA</div>
                                        <div className="text-xs opacity-90">Assistente Médica Virtual</div>
                                    </div>
                                </CardTitle>
                                <div className="flex items-center space-x-1">
                                    {/* Status indicator */}
                                    <div className={`flex items-center space-x-1 text-xs`}>
                                        <div
                                            className={`h-2 w-2 rounded-full ${
                                                connectionStatus === 'connected'
                                                    ? 'bg-green-400'
                                                    : connectionStatus === 'error'
                                                      ? 'bg-red-400'
                                                      : 'bg-yellow-400'
                                            }`}
                                        />
                                        <span className="hidden sm:inline">
                                            {connectionStatus === 'connected' ? 'Online' : connectionStatus === 'error' ? 'Erro' : 'Conectando...'}
                                        </span>
                                    </div>
                                    {messages.length > 1 && (
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            className="h-8 w-8 text-white hover:bg-white/20"
                                            onClick={clearChatHistory}
                                            title="Limpar histórico do chat"
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </Button>
                                    )}
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="h-8 w-8 text-white hover:bg-white/20"
                                        onClick={minimizeChat}
                                        title="Minimizar chat"
                                    >
                                        <Minimize2 className="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="h-8 w-8 text-white hover:bg-white/20"
                                        onClick={toggleChat}
                                        title="Fechar chat"
                                    >
                                        <X className="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </CardHeader>

                        {/* Messages */}
                        <CardContent className="flex-1 overflow-y-auto bg-gray-50 p-4">
                            <div className="space-y-2">
                                {messages.map((message, index) => renderMessage(message, index))}

                                {/* Loading indicator */}
                                {isLoading && (
                                    <div className="mb-4 flex justify-start">
                                        <div className="flex max-w-[85%] items-end space-x-2">
                                            <div className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-pink-500 text-white">
                                                <Sparkles className="h-4 w-4" />
                                            </div>
                                            <div className="ml-2 rounded-2xl rounded-bl-md bg-gray-100 px-4 py-3 shadow-sm">
                                                <div className="flex items-center space-x-2">
                                                    <div className="flex space-x-1">
                                                        <div className="h-2 w-2 animate-bounce rounded-full bg-purple-400"></div>
                                                        <div className="h-2 w-2 animate-bounce rounded-full bg-purple-400 delay-100"></div>
                                                        <div className="h-2 w-2 animate-bounce rounded-full bg-purple-400 delay-200"></div>
                                                    </div>
                                                    <span className="text-xs text-gray-500">Lívia está pensando...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                )}

                                <div ref={messagesEndRef} />
                            </div>
                        </CardContent>

                        {/* Input */}
                        <CardFooter className="border-t bg-white p-4">
                            <form onSubmit={handleSubmit} className="flex w-full gap-3">
                                <div className="relative flex-1">
                                    <Textarea
                                        ref={textareaRef}
                                        value={data.mensagem}
                                        onChange={(e) => setData('mensagem', e.target.value)}
                                        placeholder="Digite sua mensagem..."
                                        className="max-h-[120px] min-h-[42px] resize-none rounded-xl border-gray-300 pr-12"
                                        disabled={processing || isLoading}
                                        onKeyDown={(e) => {
                                            if (e.key === 'Enter' && !e.shiftKey) {
                                                e.preventDefault();
                                                handleSubmit(e);
                                            }
                                        }}
                                    />
                                    <Button
                                        type="submit"
                                        size="icon"
                                        disabled={processing || !data.mensagem.trim() || isLoading}
                                        className="absolute top-1 right-1 h-8 w-8 rounded-lg bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600"
                                    >
                                        <Send className="h-4 w-4" />
                                    </Button>
                                </div>
                            </form>
                            <div className="mt-2 text-center text-xs text-gray-500">Pressione Enter para enviar, Shift+Enter para nova linha</div>
                        </CardFooter>
                    </Card>
                </div>
            )}

            {/* Minimized chat indicator */}
            {isOpen && isMinimized && (
                <div className="fixed right-6 bottom-6 z-50">
                    <Button
                        onClick={minimizeChat}
                        className="relative h-14 w-14 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 shadow-lg hover:from-purple-600 hover:to-pink-600"
                    >
                        <MessageCircle className="h-6 w-6" />
                        <Badge className="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 p-0 text-xs text-white">
                            {messages.filter((m) => !m.isUser).length}
                        </Badge>
                    </Button>
                </div>
            )}

            {/* Floating chat button */}
            {!isOpen && (
                <div className="fixed right-6 bottom-6 z-50">
                    <Button
                        onClick={toggleChat}
                        className="group relative h-16 w-16 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 shadow-2xl transition-all duration-300 hover:scale-110 hover:from-purple-600 hover:to-pink-600"
                    >
                        <MessageCircle className="h-7 w-7 transition-transform group-hover:scale-110" />
                        <div className="absolute -top-12 right-0 rounded-lg bg-gray-900 px-3 py-1 text-xs whitespace-nowrap text-white opacity-0 transition-opacity group-hover:opacity-100">
                            Chat com Lívia IA
                        </div>
                    </Button>
                </div>
            )}
        </>
    );
}
