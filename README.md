# Blend Web App Framework

O Blend é um framework PHP para aplicações web orientadas pelo servidor. Ele possui convenções próprias para rotas, models, pages e geração de interfaces, priorizando desenvolvimento rápido e adequado à realidade brasileira.

## Princípios

- O Blend gera CRUDs dinamicamente a partir de um model e uma page, sem gerador de código.
- O framework possui tratamento nativo para formatos brasileiros, como datas, números, dinheiro, CPF e CNPJ.
- Uma aplicação pode utilizar vários bancos e tipos de banco simultaneamente.
- Atualmente, não há funcionalidade PHP classificada como legado; alguns nomes e estruturas históricas permanecem por compatibilidade.
- Todos arquivos são em minuscula e sem acentos.
- Textos em string no código são em português. Programação em inglês ( a exceção é algo brasileiro como CPF/CEP ).

## Organização dos módulos

- O padrão antigo organiza primeiro por tipo técnico, como `model/pedido` e `page/pedido`; ele continua adequado para projetos pequenos.
- O padrão atual organiza primeiro pelo módulo, como `marketplace/model` e `marketplace/page`, mantendo o domínio agrupado como um monolito modular.
- A arquitetura desejada permite que módulos se comuniquem somente por services.

## App e roteamento

- O `index.php` inicializa a `App` e chama `handle()`; a classe pode ser instanciada com `new`, acessada por `App::getInstance()` e estendida pelo projeto.
- O roteamento utiliza `p` para page, `e` para evento e `v` para valor; `cliente/editar/15` chama o evento `editar` da page de cliente com o valor `15`.
- A `App` localiza a page, executa o evento e prepara uma resposta completa ou Ajax.

## Pages

- O Blend não utiliza controllers separados: a page representa a página e exerce o papel de controller.
- Métodos públicos de uma page podem ser chamados como eventos por URL ou Ajax; métodos privados não podem.
- A autorização desses eventos depende da ACL implementada pelo projeto.
- `Page\Page` é a page básica e pode carregar um HTML para uma tela programada, inclusive com Vue.
- `Page\Crud` trabalha com um model e gera automaticamente listagem, inclusão, visualização, edição e remoção.
- `FieldLayout\Vector` gera os campos da `Page\Crud` e distribui suas larguras com classes de `col-1` a `col-12`.
- `Page\CrudDropZone` acrescenta upload, listagem e remoção de arquivos a um CRUD.
- `Page\PagePopup` permite abrir operações de uma page dentro de um popup.
- As interfaces `BeforeGridCreateRow`, `AfterGridCreateRow`, `AfterGridCreateCell` e `BeforeGridExportRow` permitem interferir na criação e exportação da grid.

## Models e DB

- Os models seguem Active Record, de forma semelhante ao Laravel, e podem ser instanciados normalmente com `new`.
- `Model::query()` cria um `Db\QueryBuilder` vinculado ao model; `new Db\QueryBuilder()` cria uma consulta independente.
- Cada model possui um esquema com colunas, relações e validators; `Db\Column\Search` permite incluir subselects como campos de busca.
- `Db\Collection` é a coleção normalmente utilizada para resultados de banco e listas de models.
- `Db\ConstantValues` transforma constantes em listas de chave e descrição, evitando valores numéricos sem significado explícito.
- `Log\IndexData` traduz nomes de índices presentes em erros de banco para mensagens compreensíveis; está em `Log` por organização histórica, mas conceitualmente pertence ao DB.

### Bancos e conexões

- Os catalogs geram o SQL específico para MySQL, Microsoft SQL Server e PostgreSQL.
- O suporte ao PostgreSQL está funcional, mas nunca foi utilizado em produção.
- A separação por catalog facilita adicionar suporte a outro banco.
- O config pode registrar várias conexões por `Db\ConnInfo`, cada uma com um identificador.
- A conexão `default` é a padrão; cada model pode selecionar outra conexão sobrescrevendo `getConnId()`.
- As conexões são abertas somente quando utilizadas, e não durante a inicialização da aplicação.

## Types e Validators

- Types representam e tratam dados no back-end; validators verificam sua validade.
- `Validator\Validator` implementa `Type\Generic`, e ambos podem ser usados no esquema e nos getters dos models.
- Entre os types estão CPF/CNPJ, data, data e hora, decimal, inteiro, dinheiro e horário.
- Há validators para CEP, CPF, CNPJ, data, e-mail, nome de arquivo, GTIN, inscrição estadual, inteiro, IP, telefone e caracteres repetidos.
- Validators podem ser instanciados com `new` para validação pelo model e também oferecem métodos auxiliares próprios.

## Services e Actions

- Services são normalmente classes estáticas e concentram operações ou regras que não pertencem à interface.
- `Db\Action` representa uma operação relacionada a um model, como disparar um e-mail em consequência de uma operação.
- `Component\Action` representa uma ação de interface, principalmente de grid, e pode chamar uma `Db\Action`.

## Views e Components

- Views são representações PHP de elementos HTML construídas sobre o PHP DOM, como `View\Div` e `View\Span`.
- `View\Ext` contém elementos extras; `View\Blend` contém elementos específicos mantidos nesse namespace histórico, embora conceitualmente também sejam extensões.
- Components geram partes mais completas da interface, como combos, grids, timelines e visualizadores de arquivos.
- Os CSS do Blend servem como referência, mas não são utilizados diretamente no projeto.

## DataHandle e DataSource

- `DataHandle` fornece acesso uniforme a GET, POST, request, server, session, user agent, files, cookies e configurações da aplicação.
- `DataSource` adapta uma origem de dados para grids e outros consumidores, controlando paginação, filtros e agregações como total, soma e média.
- As origens principais são `DataSource\Model`, `DataSource\Vector` e `DataSource\QueryBuilder`.

## Arquivos e mídia

- `Disk` oferece recursos para arquivos, uploads, pastas e JSON com tratamento de erros.
- `storage` contém arquivos temporários gerados pelo sistema, incluindo logs descartáveis; em tese, todo o seu conteúdo pode ser apagado.
- `media` contém normalmente arquivos do cliente e não deve ser tratado como temporário.
- O caminho raiz de `media` é único; alguns projetos organizam os arquivos dentro dele através de seu multi-tenancy.
- `Media\Image` lê diversos formatos e dados binários, além de ler e exportar SVG e exportar ICO.
- `Media\Color` trata cores utilizadas na manipulação de imagens.

## ReportTool

- `ReportTool` processa templates HTML e é utilizado principalmente para gerar PDFs.
- `ReportTool\Template` pode ser usado separadamente e reconhece variáveis, models e DataSources.
- `ReportTool\Engine` pode gerar PDF através de mPDF ou wkhtmltopdf por meio de `ReportTool\WkPdf`.

## Utilitários

- `Misc` reúne minimizadores de CSS e JavaScript, cálculos geográficos, otimização de arquivos e um timer utilizado para debug.

## Framework JavaScript

- `src/js/blend.js` é o núcleo JavaScript e implementa o contrato entre as pages PHP e a interface no navegador.
- A função `b()` é o manipulador DOM leve do Blend; parte do framework ainda usa jQuery, que é legado e será removido gradualmente.
- `r()` centraliza Ajax; `p()` faz Page Post, `g()` faz Page Get e `e()` chama um evento da page atual.
- A função JavaScript `p()` significa Page Post e não deve ser confundida com o parâmetro de rota `p`, que identifica a page.
- O framework envia formulários e `FormData`, controla loading, histórico, URL e rolagem.
- O PHP retorna comandos com seletor, operação e conteúdo; as operações principais são `html`, `append` e `script`.
- O array `blend.plugins` mantém plugins carregados: `register()` executa no carregamento inicial, `start()` também executa após cada Ajax e `beforeSubmit()` executa antes do envio.
- `blendJs()` executa o JavaScript específico da page e é redefinida depois do uso.
- `src/js/blend` contém comportamentos do framework e de components específicos; `src/js/plugin` contém plugins opcionais e mais gerais, ativados quando seus arquivos são carregados.