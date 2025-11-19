## BusMate — Notas rápidas para agentes AI (PHP MVC customizado)

Este projeto é um micro-framework PHP com convenções próprias. O objetivo deste arquivo é apontar padrões concretos e exemplos práticos para acelerar trabalho automático (correções, novas rotas, análise).

- **Entrypoint:** `public/index.php` — inclui `app/Libraries/Rota.php`, `Controllers.php` e `app/configuracao.php`. URLs chegam como `?url=segmento/metodo/param`.
- **Roteamento:** `app/Libraries/Rota.php` transforma `url` em `Controller` (arquivo `app/Controllers/<Nome>.php`) e chama `method(...params)` via `call_user_func_array()`.
- **Controller naming:** Arquivo `Paginas.php` => class `Paginas`. Convenção: URL `/empresa/listar/123` espera `app/Controllers/Empresa.php` com `class Empresa` e método `listar($id)`.

- **Preferência de helpers:** Controllers that `extends Controllers` podem usar `$this->view($path, $dados)` e `$this->model($name)` — ver `app/Libraries/Controllers.php`.
- **Observação:** Nem todos os arquivos em `app/Controllers/` seguem essa extensão (ex: `Usuarios.php` é uma classe procedural que usa `Usuario` model e `Database` diretamente). Trate esses como handlers independentes.

- **DB access:** `app/Libraries/Database.php` — padrão: `query($sql)`, `bind($k,$v)`, `executa()`, `resultado()` (1) / `resultados()` (many). Exemplos abundantes em `app/Controllers/Paginas.php` e `app/Controllers/Usuarios.php`.

- **Sessions / Auth:** padrão de sessão:
  - `$_SESSION['user_id']` — id do usuário
  - `$_SESSION['user_name']` — nome
  - `$_SESSION['user_tipo']` — 1=ROOT, 2=ADMIN, 3=ALUNO
  - Controllers geralmente chamam `if (session_status() === PHP_SESSION_NONE) session_start();` antes de usar.

- **Views:** `app/Views/<path>.php` — chamadas via `$this->view('paginas/contrato', $dados)`. A view usa `extract($dados, EXTR_SKIP)`, então evite colisões de nomes.

- **Uploads:** `public/uploads/` (subpastas: `usuarios_img`, `documentos`, `empresas_logo`, `onibus_img`, `usuarios_contrato`). Controllers usam `finfo_file()` para validar MIME, geram nomes únicos com `time()` + `bin2hex()` e salvam caminhos relativos como `public/uploads/...` no DB.

- **Logging / debug:** Use `app/debug_register.txt` e `app/debug_empresa.txt` para observability. Padrão: `"[YYYY-MM-DD HH:MM:SS] Mensagem\\n"` + `FILE_APPEND`. Muito código escreve arrays de `$_POST` / `$_FILES` nestes arquivos.

- **Config:** `app/configuracao.php` define `URL`, `APP_NOME`, `APP_VERSAO` e helper `formatarNome()` — evite hardcode de URL/paths.

- **Start / Dev checklist:**
  - Iniciar Apache + MySQL (XAMPP) no Windows.
  - Importar `bd_busmate.sql` no MySQL.
  - Acessar: `http://localhost/busmate/public/`.

- **Como adicionar uma rota/controller (prático):**
  1. Criar `app/Controllers/Meu.php` com `class Meu extends Controllers` (para aproveitar `$this->view()`), ou `class Meu` se quiser handler puro.
  2. Implementar `public function acao($param = null){ ... }`.
  3. Acesso via `http://.../public/index.php?url=meu/acao/param` ou com `.htaccess` amigável `/meu/acao/param`.

- **Padrões a observar ao modificar código:**
  - Use prepared statements (`Database::query()` + `bind()`), não concatene SQL.
  - Respeite validação de arquivos via `finfo_file()` e limite de tamanho já presentes (5–10MB conforme controller).
  - Ao renderizar views passe apenas dados necessários; views usam `extract()`.

Se algo aqui estiver incompleto ou você quer exemplos adicionais (ex.: trechos de `Usuarios::cadastrar` ou `Paginas::contrato`), diga quais áreas deseja que eu amplie. 

---
Arquivo criado/atualizado automaticamente: use este resumo como referência rápida para agentes que alteram o código.## BusMate — Instruções para Agentes AI (PHP MVC)

App PHP MVC com roteamento amigável, separação clara de responsabilidades e convenções específicas. Framework customizado leve — sem dependências externas.

### Arquitetura & Fluxo de Requisição
1. **Entrypoint:** `public/index.php` inicia com `ob_start()`, inclui `Rota.php` e renderiza output buffer.
2. **Roteamento:** `.htaccess` redireciona URLs para `index.php?url=segmento/metodo/parametros`.
3. **Resolução:** `Rota.php` mapeia `url` → Controller camelCase → método → parâmetros via `call_user_func_array()`.
4. **Convenção:** URL `/empresa/listar/123` → `Empresa.php::listar(123)`.

**Fluxo real:** `/paginas/contrato` → `Controllers::__construct()` instancia `Paginas`, executa `contrato()`, que renderiza view com dados PDO.

### Padrão MVC & Separação
- **Controllers** (`app/Controllers/`): Orquestram requisição, não contêm lógica de negócio.
  - Sempre `extends Controllers` para usar `$this->model()` e `$this->view()`.
  - Nome arquivo = segmento URL (ex: `Paginas.php`, `Empresas.php`, `Usuario.php`).
  - Métodos sem parâmetros internos; recebem via `$_POST`, `$_GET`, `$_SESSION`, `$_FILES`.
  
- **Models** (`app/Models/Usuario.php`): Lógica de domínio, retornam arrays `['id'=>int]` ou `['erro'=>string]`.
  - Nunca ecoam HTML, nunca usam `exit`. Erros capturados e logados via `error_log()`.
  - Todos acessos a DB usam `Database::query()` + `bind()` + `executa()` (prepared statements).
  - Exemplo: `Usuario::cadastrar($dados)` retorna `['id'=>123]` ou `['erro'=>'CPF duplicado']`.

- **Views** (`app/Views/paginas/`): Renderizam data extraída via `extract($dados, EXTR_SKIP)`.
  - Chamadas: `$this->view('paginas/contrato', ['contrato'=>obj, 'vigente'=>bool])`.
  - Variáveis locais criadas automaticamente; acesso seguro em template.

### Banco de Dados & Autenticação
- **Credenciais:** `app/Libraries/Database.php` (padrão: `root` sem senha, banco `bd_busmate`, porta 3306).
- **Conexão:** PDO com persistent connection e modo exceção (`PDO::ERRMODE_EXCEPTION`).
- **Sessão:** `$_SESSION['user_id']` e `$_SESSION['user_tipo']` (1=ROOT, 2=ADMIN, 3=ALUNO).
  - Início: `session_status() === PHP_SESSION_NONE` → `session_start()`.
  - Proteção: Controllers verificam `empty($_SESSION['user_tipo'])` antes de ação sensível.

### Uploads & Arquivos Estáticos
- **Base:** `public/uploads/` com subpastas: `empresas_logo/`, `onibus_img/`, `usuarios_img/`, `documentos/`.
- **Validação:** Sempre use `finfo_file()` para MIME (não confiar em extensão).
- **Exemplo (Empresas.php):** Valida `['image/jpeg','image/png','image/webp']`, máx 5MB, gera nome único com timestamp + `bin2hex(random_bytes(6))`.
- **URLs:** Referencie com `<?=URL?>/public/uploads/empresas_logo/arquivo.jpg`.

### Debug & Logging
- **Logs de erro:** Controllers escrevem exceções em `app/debug_empresa.txt` e `app/debug_register.txt` com timestamp.
  - Padrão: `"[" . date('Y-m-d H:i:s') . "] Mensagem\n"` com `FILE_APPEND`.
- **Erros Model:** Capturados com `catch (Throwable $t)`, logados via `error_log()`, retornam `['erro'=>'genérico']` para view.

### Configuração & Constantes Globais
- **`app/configuracao.php`:** Define `URL` (base HTTP), `APP_NOME`, `APP_VERSAO`.
- **`formatarNome()`:** Helper que normaliza nomes para Title Case (ex: "joão da silva" → "João da Silva").
- **Nunca hardcode caminhos;** sempre use `dirname(__FILE__)`, `dirname(__DIR__)`, constantes.

### Padrões & Convenções Específicas
- **Queries complexas:** Buildadas em Controller com `Database::query()` + `bind()` + `resultado()` ou `resultados()`.
- **Validação CNPJ:** Exemplo em `Usuario.php` → resolve `cnpj` para `empr_id` via `REPLACE()` no DB (remove pontos/barras/hífens).
- **Competência (AAAA-MM):** Padrão para rastreamento mensal (ex: pagamentos, viagens).
- **Vigência de contrato:** Lógica em Controller compara datas com `date('Y-m-d')` e status.

### Exemplo Completo: Fluxo de Contrato
```php
// URL: /paginas/contrato
// 1. Rota.php → Paginas.php::contrato()
// 2. Controller checa $_SESSION['user_id'] (protege com redirect a /paginas/entrar)
// 3. Query DB: SELECT contrato + empresa (INNER JOIN)
// 4. Calcula contrato_vigente (status=ATIVO, datas válidas)
// 5. Busca pagamento dessa competência
// 6. $this->view('paginas/contrato', [...])
// 7. View renderiza com extract($dados)
```

### Startup Checklist
1. XAMPP: Apache + MySQL rodando.
2. Banco: Importar `bd_busmate.sql`.
3. Arquivo: Projeto em `C:\xampp\htdocs\busmate`.
4. Acesso: `http://localhost/busmate/public/`.

### Mudanças Comuns
- **Nova rota:** Crie `app/Controllers/Meu.php` com `class Meu extends Controllers` e método `acao()`.
- **Novo upload:** Adicione pasta em `public/uploads/tipo/` e valide MIME/tamanho em Controller.
- **Nova tabela:** Dump em `bd_busmate.sql`, use `Database::query()` com prepared statements.
- **Erros:** Escreva em `app/debug_*.txt` com timestamp e contexto completo.
