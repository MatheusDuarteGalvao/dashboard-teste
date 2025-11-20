## Desafio Técnico – Dashboard de Vendas

Este projeto é um dashboard de vendas simples desenvolvido em Laravel. Ele exibe uma visão geral das vendas, uma tabela de pedidos e os produtos mais vendidos.

### Funcionalidades

- Visão geral das vendas com gráficos.
- Tabela de pedidos com detalhes como ID, cliente, valor total, status e data.
- Lista dos produtos mais vendidos.

### Requisitos

- PHP >= 8.0
- Composer
- Laravel Framework
- Banco de dados MySQL

### Instalação

1. Clone o repositório:
    ```bash
    git clone https://github.com/MatheusDuarteGalvao/dashboard-teste.git
    cd dashboard-teste
    ``` 
2. Instale as dependências via Composer:
    ```bash
    composer install
    ```

3. Configure o arquivo `.env` com as credenciais do seu banco de dados.

4. Gere a chave da aplicação:
    ```bash
    php artisan key:generate
    ```

5. Execute as migrações para criar as tabelas:
    ```bash
    php artisan migrate
    ```

6. Execute o comando de importação para popular o banco de dados com dados de exemplo:
    ```bash
    php artisan crm:import-orders --no-cache
    ```

6. Inicie o servidor de desenvolvimento:
    ```bash
    php artisan serve   
    ```

7. Acesse o dashboard em `http://localhost:8000`.
