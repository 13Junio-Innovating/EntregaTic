# EntregaTic

**EntregaTic** é um sistema de gestão de entregas e PDV (Ponto de Venda) desenvolvido para otimizar o controle de produtos, vendas, devoluções e logística de entregas. O sistema conta com funcionalidades avançadas como geração de relatórios, leitura de QR Code e exportação de dados.

## 📋 Funcionalidades Principais

- **Gestão de Produtos**: Cadastro, edição e visualização de produtos com suporte a imagens.
- **Gestão de Vendas**: PDV intuitivo para realização de vendas.
- **Controle de Entregas**: Monitoramento de status de entregas e histórico detalhado.
- **Relatórios**:
  - Relatórios de Entregas (com suporte a QR Code).
  - Relatórios de Devoluções.
  - Relatórios de Produtos.
  - Exportação para Excel (XLSX).
- **Gestão de Usuários e Fornecedores**: Controle de acesso e cadastro de parceiros.
- **Autenticação Segura**: Login, recuperação de senha e gestão de perfil.

## 🚀 Tecnologias Utilizadas

### Frontend (`app/`)
- **HTML5 / CSS3**: Estrutura e estilização responsiva.
- **JavaScript (Vanilla)**: Lógica de interação no cliente.
- **PHP**: Renderização de páginas dinâmicas.

### Backend (`backend/`)
- **PHP**: Linguagem principal do servidor.
- **Banco de Dados**: MySQL (estrutura definida em scripts SQL, ex: `mysql/pdv.sql` se disponível).
- **Bibliotecas**:
  - `PHPOffice/PhpSpreadsheet`: Para geração e manipulação de planilhas Excel.
  - `chillerlan/php-qrcode`: Para geração de QR Codes.

## 📂 Estrutura do Projeto

```
EntregaTic/
├── app/                  # Aplicação Web (Frontend e Controllers de View)
│   ├── assets/           # Imagens e recursos estáticos
│   ├── config/           # Configurações (ex: .env)
│   ├── controllers/      # Controladores da aplicação
│   ├── css/              # Folhas de estilo
│   ├── js/               # Scripts JavaScript
│   ├── layouts/          # Componentes de layout (cabeçalho, menu)
│   ├── report/           # Scripts de geração de relatórios
│   └── web/              # Páginas públicas do sistema
│
├── backend/              # Núcleo da Lógica de Negócios e Bibliotecas
│   └── app/
│       └── utils/        # Utilitários e bibliotecas (QRCode, Spreadsheet)
│
└── .htaccess             # Configuração do servidor Apache
```

## ⚙️ Instalação e Configuração

1. **Requisitos**:
   - Servidor Web (Apache/Nginx) com suporte a PHP 7.4+.
   - Banco de dados MySQL/MariaDB.
   - Composer (opcional, caso precise atualizar dependências).

2. **Configuração do Banco de Dados**:
   - Importe o script de banco de dados (se disponível) para o seu servidor MySQL.
   - Configure as credenciais de acesso no arquivo de conexão (`app/controllers/db_connection.php` ou similar).

3. **Configuração do Ambiente**:
   - Certifique-se de que o arquivo `.htaccess` está ativo para o roteamento correto de URLs.
   - Verifique as permissões de escrita nas pastas de upload (`app/assets/img/products`).

4. **Execução**:
   - Acesso o sistema através do navegador: `http://localhost/EntregaTic/app/web/login.php` (ou a URL configurada no seu ambiente).

## 📄 Licença

Este projeto está sob a licença [MIT](LICENSE) (ou a licença definida pelo repositório).

---
Desenvolvido por **13Junio-Innovating**.
