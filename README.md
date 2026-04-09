# Gestão de Equipe — HRMS
## Sistema de Gestão de RH | Santander Style

---

## Stack

| Camada      | Tecnologia                         |
|-------------|-------------------------------------|
| Backend     | PHP 8.2 + Laravel 11                |
| Frontend    | Livewire 3 + Alpine.js + Tailwind 4 |
| Banco       | MySQL 8+                            |
| Auth & ACL  | Laravel Auth + Spatie Permission    |
| Multi-tenant| Coluna `tenant_id` + Subdomain      |

---

## Instalação

```bash
# 1. Copiar .env
cp .env.example .env

# 2. Configurar banco no .env
DB_DATABASE=gestao_equipe
DB_USERNAME=root
DB_PASSWORD=

# 3. Instalar dependências
composer install
npm install

# 4. Gerar chave
php artisan key:generate

# 5. Rodar migrations + seed
php artisan migrate --seed

# 6. Compilar assets
npm run build

# 7. (opcional) Link storage
php artisan storage:link
```

---

## Credenciais de Demo (após seed)

| Role       | E-mail                          | Senha      |
|------------|----------------------------------|------------|
| SuperAdmin | superadmin@gestaoequipe.com      | password   |
| Admin      | admin@demo.com                   | password   |
| Manager    | manager@demo.com                 | password   |
| Employee   | funcionario@demo.com             | password   |

---

## Multi-tenancy

O sistema identifica o tenant por **subdomínio** ou **header HTTP**:

```
acme.gestaoequipe.test  →  slug = "acme"
X-Tenant-Slug: acme     →  header fallback (APIs/Postman)
```

Configure `/etc/hosts` (macOS/Linux) para desenvolvimento local:

```
127.0.0.1   demo.gestaoequipe.test
127.0.0.1   gestaoequipe.test
```

---

## Estrutura de Arquivos

```
app/
├── Http/
│   ├── Controllers/Auth/AuthenticatedSessionController.php
│   └── Middleware/
│       ├── TenantMiddleware.php          ← resolve tenant
│       └── EnsureTenantScope.php         ← garante isolamento
├── Livewire/
│   ├── Admin/AdminDashboard.php
│   ├── Employee/EmployeeDashboard.php
│   ├── Employee/EmployeeList.php
│   └── Vacation/
│       ├── VacationRequestList.php
│       └── VacationRequestForm.php
├── Models/
│   ├── Tenant.php
│   ├── User.php, Employee.php
│   ├── Department.php, JobPosition.php
│   ├── VacationRequest.php, Absence.php
│   ├── JobOpening.php, JobCandidate.php
│   └── EmployeeDocument.php, EmployeePositionHistory.php
├── Notifications/
│   ├── VacationRequestSubmittedNotification.php
│   ├── VacationRequestApprovedNotification.php
│   └── VacationRequestRejectedNotification.php
├── Policies/
│   ├── VacationRequestPolicy.php      ← isolamento de dados
│   └── EmployeePolicy.php
├── Providers/AppServiceProvider.php    ← register policies
└── Services/
    ├── VacationApprovalService.php     ← business logic CLT
    └── DashboardMetricsService.php     ← métricas RH
database/
├── migrations/  (12 arquivos ordenados)
└── seeders/DatabaseSeeder.php
docs/ERD.txt                            ← diagrama ERD textual
resources/
├── css/app.css                         ← design system Santander
└── views/
    ├── layouts/{app,auth}.blade.php
    ├── auth/login.blade.php
    ├── livewire/admin/admin-dashboard.blade.php
    ├── livewire/vacation/...
    └── livewire/employee/...
```

---

## Paleta de Cores (Santander Style)

| Token                  | Hex       |
|------------------------|-----------|
| `santander-red`        | `#EC0000` |
| `santander-red-dark`   | `#B30000` |
| `neutral-bg`           | `#F5F5F5` |
| `neutral-text`         | `#444444` |
| `neutral-muted`        | `#888888` |
| `neutral-sidebar`      | `#1C1C1E` |

---

## Roles & Permissões

| Role        | Capacidades                                              |
|-------------|----------------------------------------------------------|
| super_admin | Tudo — gerencia tenants, acesso irrestrito               |
| admin       | CRUD completo dentro do seu tenant                       |
| manager     | Aprova/rejeita férias, visualiza equipe subordinada      |
| employee    | Portal próprio: saldo de férias, solicitar e acompanhar  |

---

## Módulos Implementados no MVP

- [x] Auth corporativa (login/logout)
- [x] Multi-tenancy (subdomain + tenant_id)
- [x] Dashboard Admin com métricas (headcount, turnover, alertas)
- [x] Módulo de Férias completo (solicitação, aprovação, rejeição, cancelamento)
- [x] CRUD de Colaboradores com modal
- [x] Portal do Colaborador (saldo, histórico de pedidos)
- [x] Notificações por e-mail + database
- [x] Policies de isolamento de dados
- [x] Seed completo com dados de demo

## Próximos Passos (Roadmap)

- [ ] Calendário de escala de férias (FullCalendar.io)
- [ ] Módulo de Afastamentos (CRUD + upload de documentos)
- [ ] ATS completo (pipeline Kanban de candidatos)
- [ ] Relatórios exportáveis (PDF/XLSX)
- [ ] FilamentPHP admin panel (recomendado para v2)
- [ ] PWA / notificações push para mobile
