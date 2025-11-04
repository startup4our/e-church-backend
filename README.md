# E-Church Backend

## Documento de Requisitos (DRE)

O **Documento de Requisitos Específico (DRE)** detalha todas as funcionalidades, fluxos e regras de negócio do sistema **E-Church Backend**.  
Ele serve como referência técnica e funcional para o desenvolvimento, manutenção e futuras expansões do projeto.

📄 [Acessar DRE (GPS – PDF)](./DRE%20GPS.pdf)

## Descrição

O **E-Church Backend** é uma aplicação backend desenvolvida em Laravel para gerenciar sistemas de igrejas digitais. Ele fornece uma API robusta para funcionalidades como agendamento de eventos, gerenciamento de usuários, chats, gravações, músicas, permissões e muito mais. Projetado para ser escalável e seguro, utilizando autenticação JWT e outras tecnologias modernas.

## Funcionalidades

- **Gerenciamento de Igrejas**: Criação e administração de igrejas e suas áreas.
- **Usuários e Permissões**: Sistema de usuários com roles e permissões personalizáveis.
- **Agendamento**: Gerenciamento de horários, exceções de datas e indisponibilidades.
- **Chats e Mensagens**: Sistema de comunicação em tempo real.
- **Gravações e Links**: Armazenamento e compartilhamento de gravações e links.
- **Músicas**: Biblioteca de músicas para cultos e eventos.
- **Autenticação**: Suporte a JWT para autenticação segura.
- **API RESTful**: Endpoints bem documentados para integração com frontends.

## Tecnologias Utilizadas

- **Laravel**: Framework PHP para desenvolvimento web.
- **JWT**: Para autenticação de usuários.
- **MySQL/PostgreSQL**: Banco de dados relacional.
- **Composer**: Gerenciamento de dependências PHP.
- **PHPUnit**: Para testes unitários e de integração.

## Instalação

### Pré-requisitos

- PHP 8.0 ou superior
- Composer
- MySQL ou PostgreSQL
- Node.js (opcional, para assets)

### Passos para Instalação

1. **Clone o repositório:**
   ```bash
   git clone https://github.com/startup4our/e-church-backend.git
   cd e-church-backend
   ```

2. **Instale as dependências:**
   ```bash
   composer install
   ```

3. **Configure o ambiente:**
   - Copie o arquivo `.env.example` para `.env`:
     ```bash
     cp .env.example .env
     ```
   - Edite o `.env` com suas configurações de banco de dados, JWT, etc.

4. **Gere a chave JWT:**
   ```bash
   php artisan jwt:secret
   ```

5. **Gere a chave da aplicação:**
   ```bash
   php artisan key:generate
   ```

6. **Execute as migrações:**
   ```bash
   php artisan migrate
   ```

7. **Opcional: Execute os seeders para dados iniciais:**
   ```bash
   php artisan db:seed
   ```

8. **Inicie o servidor:**
   ```bash
   php artisan serve
   ```

A aplicação estará disponível em `http://localhost:8000`.

## Uso

### Endpoints da API

- `GET /api/churches` - Lista todas as igrejas
- `POST /api/users` - Cria um novo usuário
- `GET /api/schedules` - Lista agendamentos
- E muito mais. Consulte a documentação completa da API.

### Autenticação

Use o endpoint `/api/login` para obter um token JWT, e inclua-o no header `Authorization: Bearer <token>` para requisições autenticadas.

## Testes

Execute os testes com PHPUnit:
```bash
./vendor/bin/phpunit
```

## Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

## Licença

Este projeto está licenciado sob a MIT License. Veja o arquivo `LICENSE` para mais detalhes.

## Contato

Para dúvidas ou suporte, entre em contato com a equipe em [email@startup4our.com](mailto:email@startup4our.com).
