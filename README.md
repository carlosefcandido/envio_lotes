# Sistema de Envio de Lotes

Este é um sistema de envio de lotes que permite aos operadores registrar movimentos financeiros e aos supervisores visualizar relatórios desses movimentos. O sistema inclui funcionalidades para selecionar bancos, filtrar lançamentos por data e exportar dados para arquivos XLS.

## Funcionalidades

- **Operador:**
  - Login e autenticação.
  - Registro de movimentos financeiros com seleção de tipo de pagamento e banco.
  - Visualização de lançamentos diários.
  - Edição de lançamentos existentes.

- **Supervisor:**
  - Login e autenticação.
  - Visualização de relatórios de movimentos financeiros.
  - Filtragem de lançamentos por intervalo de datas.
  - Exportação de dados para arquivos XLS.
  - Marcação de lançamentos como enviados.

## Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache, Nginx, etc.)
- Extensão PHP MySQLi

## Instalação

1. Clone o repositório para o seu servidor web:
   ```bash
   git clone https://github.com/carlosefcandido/envio_lotes.git
2. Navegue até o diretório do projeto:
  cd sistema-envio-lotes

3. Crie o banco de dados MySQL e importe o arquivo sql.sql:
```SQL
    CREATE DATABASE envio_lotes;
    USE envio_lotes;
    SOURCE sql.sql;
```
5. Configure a conexão com o banco de dados no arquivo conexao.php:
  ```php
  <?php
  function conectar() {
      $servername = "localhost";
      $username = "seu_usuario";
      $password = "sua_senha";
      $dbname = "envio_lotes";
  
      $conn = new mysqli($servername, $username, $password, $dbname);
  
      if ($conn->connect_error) {
          die("Connection failed: " . $conn->connect_error);
      }
  
      return $conn;
  }
  ?>
```
5. Configure as credenciais de autenticação no arquivo auth.php:
```php
   <?php
      session_start();
      
      function verificaLogin() {
          if (!isset($_SESSION['usuario'])) {
              header("Location: login.php");
              exit();
          }
      }
    ?>
```

## Uso
- **Operador**
1. Faça login como operador.
2. Registre novos movimentos financeiros preenchendo os campos obrigatórios.
3. Visualize e edite lançamentos diários.
- **Supervisor**
1. Faça login como supervisor.
2. Visualize o relatório de movimentos financeiros.
3. Filtre lançamentos por intervalo de datas.
4. Exporte os dados para arquivos XLS.
5. Marque lançamentos como enviados.

## Estrutura do Projeto
  * ```conexao.php:``` Arquivo de configuração da conexão com o banco de dados.
  * ```auth.php:``` Arquivo de autenticação e verificação de login.
  * ```operador.php:``` Interface e funcionalidades para operadores.
  * ```supervisor.php:``` Interface e funcionalidades para supervisores.
  * ```movimento.php:``` Processamento de registros de movimentos financeiros.
  * ```exportar_lancamentos.php:``` Exportação de dados para arquivos XLS.
  * ```styles:``` Diretório contendo arquivos CSS para estilização.
    
## Contribuição
  1. Faça um fork do projeto.
  2. Crie uma branch para sua feature (git checkout -b feature/nova-feature).
  3. Commit suas mudanças (git commit -am 'Adiciona nova feature').
  4. Faça push para a branch (git push origin feature/nova-feature).
  5. Abra um Pull Request.
## Licença
Este projeto está licenciado sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.
