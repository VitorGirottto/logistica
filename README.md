# Sistema de Logística

Sistema completo de gestão logística desenvolvido em PHP puro, HTML, CSS e JavaScript.

## Funcionalidades

- ✅ Sistema de login com autenticação
- ✅ Gestão de usuários com perfil editável
- ✅ CRUD completo para entregas, motoristas e veículos
- ✅ 12 dashboards dinâmicos com dados reais
- ✅ Layout responsivo com menu lateral
- ✅ Design roxo e amarelo
- ✅ Proteção contra SQL Injection
- ✅ Sistema de sessões seguro

## Tecnologias Utilizadas

- **Backend**: PHP 7.4+
- **Frontend**: HTML5, CSS3, JavaScript
- **Banco de Dados**: MySQL 5.7+
- **Gráficos**: Chart.js
- **Ícones**: Font Awesome

## Instalação

### 1. Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Apache ou Nginx
- Extensões PHP: PDO, PDO_MySQL

### 2. Configuração do Banco de Dados

1. Crie um banco de dados MySQL:
\`\`\`sql
CREATE DATABASE sistema_logistica;
\`\`\`

2. Execute o script SQL localizado em `database/schema.sql`

3. Configure a conexão no arquivo `config/database.php`:
\`\`\`php
private $host = 'localhost';
private $db_name = 'sistema_logistica';
private $username = 'seu_usuario';
private $password = 'sua_senha';
\`\`\`

### 3. Configuração do Servidor

1. Clone ou baixe os arquivos para o diretório do servidor web
2. Configure as permissões adequadas
3. Acesse o sistema pelo navegador

### 4. Login Padrão

- **E-mail**: admin@logistica.com
- **Senha**: password

## Estrutura do Projeto

\`\`\`
sistema-logistica/
├── config/
│   ├── config.php
│   └── database.php
├── css/
│   ├── style.css
│   └── login.css
├── database/
│   └── schema.sql
├── includes/
│   ├── sidebar.php
│   └── verificaLogin.php
├── js/
│   └── dashboard.js
├── dashboard.php
├── entregas.php
├── motoristas.php
├── veiculos.php
├── roteirizacao.php
├── perfil.php
├── login.php
├── logout.php
├── index.php
├── .htaccess
└── README.md
\`\`\`

## Dashboards Disponíveis

1. **Entregas por região** - Gráfico de rosca
2. **Entregas atrasadas** - Contador
3. **Tempo médio de entrega** - Métrica em horas
4. **Custo por rota** - Gráfico de barras
5. **Frota ativa** - Contador
6. **Motoristas por turno** - Gráfico de pizza
7. **Ocorrências por tipo** - Gráfico de barras
8. **Consumo de combustível** - Cálculo baseado em KM
9. **KM rodado por mês** - Gráfico de linha
10. **Veículos em manutenção** - Contador
11. **Produtividade por motorista** - Gráfico horizontal
12. **Eficiência de roteirização** - Percentual

## Funcionalidades CRUD

### Entregas
- Criar nova entrega
- Editar entrega existente
- Excluir entrega
- Visualizar lista completa
- Filtros e busca

### Motoristas
- Cadastrar motorista
- Editar dados do motorista
- Ativar/desativar motorista
- Excluir motorista (se não tiver entregas)

### Veículos
- Cadastrar veículo
- Editar informações do veículo
- Alterar status (Ativo/Inativo/Manutenção)
- Excluir veículo (se não tiver entregas)

### Usuários
- Editar perfil próprio
- Alterar senha
- Excluir conta própria

## Segurança

- Prepared statements para prevenir SQL Injection
- Validação e sanitização de dados
- Sistema de sessões seguro
- Proteção de arquivos sensíveis via .htaccess
- Headers de segurança configurados

## Personalização

### Cores do Tema
As cores principais podem ser alteradas no arquivo `css/style.css`:
- Roxo: `#8B5CF6`
- Amarelo: `#F59E0B`

### Adicionando Novos Dashboards
1. Adicione a consulta SQL na página desejada
2. Crie o elemento canvas no HTML
3. Configure o gráfico com Chart.js no JavaScript

## Suporte

Para dúvidas ou problemas:
1. Verifique os logs de erro do PHP
2. Confirme as configurações do banco de dados
3. Verifique as permissões de arquivo

## Licença

Este projeto é de uso livre para fins educacionais e comerciais.
\`\`\`

Este sistema de logística completo inclui todas as funcionalidades solicitadas:

✅ **Sistema de Login** - Autenticação segura com sessões
✅ **Gestão de Usuários** - Perfil editável e exclusão de conta
✅ **Layout Responsivo** - Menu lateral fixo com design roxo/amarelo
✅ **12 Dashboards Dinâmicos** - Todos com dados reais do MySQL
✅ **CRUD Completo** - Para entregas, motoristas e veículos
✅ **Banco de Dados MySQL** - Com estrutura completa e dados de exemplo
✅ **Segurança** - Prepared statements e proteções
✅ **Organização** - Estrutura de pastas bem definida

O sistema está pronto para uso e pode ser facilmente expandido com novas funcionalidades!
