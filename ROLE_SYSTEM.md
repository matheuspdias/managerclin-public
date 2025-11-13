# Sistema de Roles e Permissões

Este documento explica o sistema completo de roles e permissões implementado na aplicação, incluindo proteção de rotas no backend e frontend.

## Tipos de Roles

### 🔑 **ADMIN** - Administrador
**Acesso total a todos os módulos:**
- ✅ Dashboard
- ✅ Financeiro
- ✅ Pacientes
- ✅ Colaboradores
- ✅ Serviços
- ✅ Consultórios
- ✅ Agendamentos
- ✅ Controle de Estoque
- ✅ Atestados Médicos
- ✅ Faturamento
- ✅ Créditos de IA
- ✅ Configurações

### 👋 **RECEPTIONIST** - Recepcionista
**Acesso limitado aos módulos operacionais:**
- ✅ Dashboard
- ✅ Pacientes
- ✅ Colaboradores
- ✅ Serviços
- ✅ Consultórios
- ✅ Agendamentos
- ✅ Controle de Estoque
- ❌ Financeiro
- ❌ Atestados Médicos
- ❌ Faturamento
- ❌ Créditos de IA
- ❌ Configurações

### 🩺 **DOCTOR** - Médico
**Acesso focado no atendimento:**
- ✅ Dashboard
- ✅ Pacientes
- ✅ Serviços
- ✅ Consultórios
- ✅ Agendamentos
- ✅ Atestados Médicos
- ❌ Colaboradores
- ❌ Controle de Estoque
- ❌ Financeiro
- ❌ Faturamento
- ❌ Créditos de IA
- ❌ Configurações

### 💰 **FINANCE** - Financeiro
**Acesso focado na gestão financeira:**
- ✅ Dashboard
- ✅ Financeiro
- ✅ Controle de Estoque
- ✅ Faturamento
- ✅ Créditos de IA
- ❌ Pacientes
- ❌ Colaboradores
- ❌ Serviços
- ❌ Consultórios
- ❌ Agendamentos
- ❌ Atestados Médicos
- ❌ Configurações

## Como Funciona

### 1. Model Role
O model `Role` contém métodos para verificar acesso:
- `canAccess(string $resource): bool` - Verifica se pode acessar um recurso
- `getAllowedResources(): array` - Retorna todos os recursos permitidos
- `isAdmin(): bool` - Verifica se é administrador

### 2. Middleware CheckResourcePermission
O middleware `CheckResourcePermission` protege as rotas no backend:
- Verifica se o usuário está autenticado
- Confirma se o usuário tem uma role definida
- Usa `Role->canAccess($resource)` para validar permissão
- Retorna erro 403 personalizado quando o acesso é negado
- Registrado como alias `'can.access'` no bootstrap

### 3. Hook useMenuPermissions
O hook `useMenuPermissions()` controla o menu lateral:
- Filtra itens do menu baseado na role do usuário
- Retorna apenas os módulos permitidos
- Utiliza regras fixas definidas em `ROLE_ACCESS_RULES`

### 4. Menu Lateral Dinâmico
O componente `AppSidebar` usa o hook para:
- Mostrar apenas módulos permitidos para cada role
- Mapear ícones corretamente
- Manter performance com useMemo

## Regras de Validação

As regras são definidas de forma fixa no código:

```typescript
const ROLE_ACCESS_RULES = {
    ADMIN: [
        'dashboard', 'financial', 'patients', 'users', 'services',
        'rooms', 'appointments', 'inventory', 'medical-certificates',
        'billing', 'ai-credits', 'settings'
    ],
    RECEPTIONIST: [
        'dashboard', 'patients', 'users', 'services',
        'rooms', 'appointments', 'inventory'
    ],
    DOCTOR: [
        'dashboard', 'patients', 'services',
        'rooms', 'appointments', 'medical-certificates'
    ],
    FINANCE: [
        'dashboard', 'financial', 'inventory',
        'billing', 'ai-credits'
    ]
};
```

## Proteção de Rotas

### 1. Rotas Protegidas no Backend
Todas as rotas principais são protegidas com middleware `can.access:recurso`:

```php
// Exemplos de rotas protegidas
Route::group(['prefix' => 'patients', 'middleware' => 'can.access:patients'], function () {
    Route::get('/', [CustomerController::class, 'index']);
    // ... outras rotas
});

Route::group(['prefix' => 'financial', 'middleware' => 'can.access:financial'], function () {
    Route::get('/', [FinancialController::class, 'index']);
    // ... outras rotas
});
```

### 2. Fluxo de Proteção
1. Usuário acessa URL diretamente (ex: `/financial`)
2. Middleware `CheckResourcePermission` é executado
3. Verifica autenticação e role do usuário
4. Chama `$user->role->canAccess('financial')`
5. Se permitido: continua para o controller
6. Se negado: retorna erro 403 personalizado

### 3. Página de Erro 403
- Página personalizada em `resources/js/pages/Errors/403.tsx`
- Aceita mensagens personalizadas do middleware
- Botão para retornar ao dashboard

## Configuração de Usuários

### 1. Seeder Automático
O `RoleSeeder` cria automaticamente as 4 roles:
- Administrador (ADMIN)
- Recepcionista (RECEPTIONIST)
- Médico (DOCTOR)
- Financeiro (FINANCE)

### 2. Atribuição de Roles
Para atribuir uma role a um usuário:
```php
$user = User::find(1);
$role = Role::where('type', 'DOCTOR')->first();
$user->role_id = $role->id;
$user->save();
```

### 3. Verificação no Frontend
Para verificar acesso no React:
```tsx
import { useMenuPermissions } from '@/hooks/use-menu-permissions';

function MyComponent() {
    const { canAccess, isAdmin } = useMenuPermissions();

    return (
        <div>
            {canAccess('patients') && (
                <Link href="/patients">Pacientes</Link>
            )}

            {isAdmin && (
                <Link href="/settings">Configurações</Link>
            )}
        </div>
    );
}
```

## Vantagens do Sistema

✅ **Segurança Completa**: Proteção tanto no frontend quanto backend
✅ **Simplicidade**: Regras fixas, fáceis de entender
✅ **Performance**: Sem consultas complexas ao banco
✅ **Manutenibilidade**: Código centralizado e limpo
✅ **Experiência do Usuário**: Interface limpa sem elementos inacessíveis
✅ **Escalabilidade**: Fácil de adicionar novos recursos
✅ **Proteção contra URL direta**: Impede acesso não autorizado via URL

## Limitações

❌ **Flexibilidade**: Não permite configuração granular por empresa
❌ **Personalização**: Regras fixas para todos os clientes
❌ **Auditoria**: Não há log de mudanças de permissão

## Estrutura de Arquivos

```
# Backend - Models e Middleware
app/Models/Role.php                          # Model com regras de acesso
app/Http/Middleware/CheckResourcePermission.php  # Middleware de proteção
bootstrap/app.php                            # Registro do middleware

# Frontend - Hooks e Componentes
resources/js/hooks/use-menu-permissions.ts  # Hook para verificação de permissões
resources/js/components/app-sidebar.tsx     # Menu lateral dinâmico
resources/js/pages/Errors/403.tsx          # Página de erro personalizada

# Database
database/seeders/RoleSeeder.php             # Criação automática das roles

# Rotas
routes/web.php                              # Rotas protegidas com middleware
routes/settings.php                         # Rotas de configurações
```

## Recursos Protegidos

| Recurso | Rota Protegida | Middleware Applied |
|---------|---------------|-------------------|
| **Pacientes** | `/patients/*` | `can.access:patients` |
| **Usuários** | `/users/*` | `can.access:users` |
| **Agendamentos** | `/appointments/*` | `can.access:appointments` |
| **Serviços** | `/services/*` | `can.access:services` |
| **Consultórios** | `/rooms/*` | `can.access:rooms` |
| **Financeiro** | `/financial/*` | `can.access:financial` |
| **Atestados** | `/medical-certificates/*` | `can.access:medical-certificates` |
| **Faturamento** | `/billing/*` | `can.access:billing` |
| **Créditos IA** | `/ai-credits/*` | `can.access:ai-credits` |
| **Estoque** | `/inventory/*` | `can.access:inventory` |
| **WhatsApp Config** | `/settings/whatsapp` | `can.access:settings` |

## Implementações Concluídas

✅ **Validações backend** nos controllers via middleware
✅ **Middleware** para proteção completa de rotas
✅ **Página de erro 403** personalizada
✅ **Menu lateral** responsivo às permissões
✅ **Hook de permissões** para uso no frontend
✅ **Proteção contra acesso direto via URL**

## Próximos Passos Sugeridos

1. **Criar componente** para seleção de roles no cadastro de usuários
2. **Implementar logs** de acesso por módulo
3. **Adicionar testes** para validação das regras
4. **Implementar cache** para otimizar verificações de permissão
5. **Adicionar auditoria** de mudanças de roles