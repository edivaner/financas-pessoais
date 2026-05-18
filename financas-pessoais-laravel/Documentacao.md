Sistema Financeiro - Documentação
📦 Classes e Entidades do Domínio

### Classe	Responsabilidade	Requisitos

## Descrições das classes e requisitos

### User (Usuários)
Classe responsável por criar e gerenciar o login do usuário no sistema através de e-mail e senha. A combinação de e-mail e senha deve ser única.

Deve existir uma classe Email para enviar mensagens ao e-mail selecionado para validação. O sistema gerará automaticamente um código de validação temporário, válido por 10 minutos. Este código pode conter números e letras. O usuário deverá visualizar o e-mail e inserir o código em uma tela específica criada pelo sistema. Se o código estiver correto, será liberada a criação do usuário.

O mesmo procedimento se aplica ao login: será enviado um código por e-mail e o acesso será liberado apenas se estiver correto.

O campo staff indica se o usuário é administrador do sistema. O campo cargo, vinculado a um enum, define o cargo do usuário: ADM, SUPORTE ou CLIENTE. Todos os usuários criados serão clientes por padrão, sem possibilidade de alteração, exceto para usuários staff e ADM.

O usuário deve ser salvo em cache para não perder a conectividade e não precisar realizar login toda vez que acessar o aplicativo.

### Lançamentos
Classe responsável por gerenciar as movimentações financeiras do sistema.

O usuário logado poderá adicionar lançamentos clicando em um símbolo de “+”, exibindo todas as opções disponíveis: Receitas, Despesas, Transferências, Investimentos.

# Regras por tipo de lançamento:
    1 - Receitas: o usuário deve escolher a conta de origem, informar o parcelamento (caso seja gasto de cartão, também deve informar qual cartão será utilizado).
        1.2 - Se não houver parcelamento, o lançamento será “à vista”.

        1.3 - Se houver parcelamento, o sistema dividirá o valor em várias parcelas, criando lançamentos correspondentes, atualizando o campo parcelaAtual.

        1.4 - Exemplo: valor de R$150 parcelado em 3x → R$50 no dia atual e R$50 para os próximos 30 dias cada, com parcelaAtual indicando a parcela correspondente.

    2 - Cartões: ao selecionar um cartão, o sistema pergunta se o lançamento será débito ou crédito.
        2.1 - Débito: valor será subtraído do campo saldo da conta vinculada.

        2.2 - Crédito: valor será adicionado ao campo faturaTotal do cartão.

    3 - Data do lançamento: por padrão, a data é a atual, mas pode ser alterada. Se a data for futura, o saldo não será alterado e o campo estaPago ficará como false. Quando a data atual atingir a data do lançamento, o saldo será atualizado e estaPago passará a true.

    4 - Investimentos: o sistema pergunta se a movimentação será débito ou depositar.

        4.1 - Débito: valor é removido do campo saldoInvestido e adicionado ao saldo da conta de origem.

        4.2 - Depositar: valor é adicionado ao saldoInvestido da conta selecionada.

    5 - Transferência: é necessário informar a conta de origem (de onde sai o dinheiro) e a conta de destino (para onde vai o valor).

        5.1 - Existe a opção “Enviar para saldo investidor”, transferindo o valor do saldo de uma conta para o saldoInvestido de qualquer conta.

    6 - Despesas: o valor é subtraído do saldo da conta de origem.


## Regras gerais:
1. Ao deletar um lançamento, a movimentação realizada por ele deve ser revertida.
2. Ao editar, apenas o valor pode ser alterado; contas de origem e destino permanecem bloqueadas. O saldo das contas/cartões deve ser atualizado de acordo com a diferença entre o valor anterior e o novo.
3. Categoria é opcional. Se escolhida, o campo valorGastoAtual do limite associado a categoria deve ser atualizado.
4. O usuário pode anexar imagens comprovando o lançamento. Essas imagens serão salvas na tabela Imagem com o tipo LANCAMENTO.
5. O campo estaPago é true por padrão, mas será false se a data for futura.
6. O campo simulado marca lançamentos que não impactam saldos reais, atualizando apenas o saldoSimulado.
7. Tem de haver um campo chamado idUser, que será vinculado informando o id do user que o criou este lançamento.

### Contas
Classe que representa instituições financeiras ou carteira (dinheiro em espécie).
1. Campo carteira (boolean): indica se é dinheiro em espécie.
2. Campo saldo: valor disponível para transações.
3. Campo saldoInvestido: saldo investido, separado do saldo disponível.
4. Campo nome: nome da conta informado pelo usuário.
5. Campo imagem: escolhida no banco de dados, do tipo LOGO.
6. Campo somarTelaInicial: indica se o saldo da conta será exibido e somado aos “Totais das contas” na tela inicial.
7. Tem de haver um campo chamado idUser, que será vinculado informando o id do user que criou a conta.

### Cartões
Representa cartões de crédito, débito ou múltiplos.
1. O sistema mantém a fatura atual, atualizada automaticamente conforme lançamentos de crédito.
2. Débito: valores são retirados do saldo da conta vinculada ao cartão.
3. Campo imagem: escolhida no banco de dados, tipo LOGO.
4. Tem de haver um campo chamado idUser, que será vinculado informando o id do user que criou este cartão.

### Imagem
Classe para gerenciar imagens no sistema.
1. Campo caminhoImagem: localização da imagem.
2. Campo tipoImagem: define se é LOGO (cartões/contas) ou LANCAMENTO (comprovantes).
3. Tem de haver um campo chamado idUser, que será vinculado informando o id do user que adicionou essa imagem, por exemplo, imagem de perfil, foi adicionado por um usuário, então tem que preencher o campo.

### Limites
Classe para gerenciar gastos por categoria.
1. Cada limite é vinculado a uma categoria, com valor máximo (valorLimite).
2. Toda vez que um lançamento de despesa ocorre, o valor é somado ao valorGastoAtual.
3. Indicador de progresso por cor: verde (<70%), amarelo (>=70% e <100%), vermelho (>=100%).
4. O valor de gastos é resetado todo dia 01, sem apagar dados históricos (zerando apenas valorGastoAtual).
5. Tem de haver um campo chamado idUser, que será vinculado informando o id do user que o criou.

### Categorias
Representa categorias de lançamentos: DESPESAS, CRÉDITO, INVESTIMENTOS.
1. Campos: nome, cor, ícone (FontAwesome), idUser.
2. Campo ativo: se false, a categoria não aparece como opção.
3. Algumas categorias padrão podem ser criadas via seeders: Academia, Alimentação, Assinaturas, Bebidas, Bem-estar, Compras e Lazer, Educação, Emergências, Empréstimos, Entretenimento Digital, Higiene, Hobbies, Impostos e Taxas, Investimentos, Manutenção e Reparos, Mercado, Moradia, Outros, Pagamentos, Poupança, Renda Extra, Salário, Saúde, Seguros, Serviços Bancários, Streaming, Transferências, Transporte, Viagem.
4. O idUser será vinculado informando o id do user que o criou.

### SubCategorias
Ligadas a uma categoria para detalhar lançamentos.
1. Campos: nome, cor, ícone (FontAwesome), ativo, idUser.
2. Podem existir subcategorias padrão vinculadas a categorias, ex.:
3. Padaria, Mercearia (vinculado a categoria Mercado)
4. Seguro de automóvel, Seguro de vida, Seguro residencial (vinculado a categoria Seguros)
5. Reparos de eletrodomésticos e reparos domésticos (vinculado a categoria Manutenção e Reparos)
6. IPTU, IR, IPVA (vinculado a categoria Impostos e Taxas)
7. Despesas emergenciais (vinculado a categoria Emergências)
8. Cursos, Livros, Mensalidades escolares (vinculado a categoria Educação)
9. Cartão de crédito, Empréstimos pessoais, Financiamento (vinculado a categoria Empréstimos)
10. Cafeteria, Delivery, Supermercado, Lanchonete (vinculado a categoria Alimentação)
11. O idUser será vinculado informando o id do user que o criou.

# Descrições das telas
Essas descrições estão relacionadas as telas do sistema. Tela inicial, Lançamento, Balanço gerl, e perfil e cadastros são menus, que na versão aplicativo ficará exibida no rodapé da página, e na versão web ficará exibida no menu lateral.

## Login 
Deve haver um card centralizado pedindo email e senha, em baixo dois botoes, o primeiro é Cadastrar e o segundo é login. 
1. No botão cadastrar, irá pedir um email de cadastro, e depois a senha. 
    1.1 -  Deve enviar uma mensagem para o email informado, essa mensagem deve conter o código para verificar na tela de cadastro.
    1.2 - Se este codigo estiver correto, vai gravar no banco de dados o email e senha do usuário, assim criando um usuário válido.
    1.3 - E com a senha e o email, irá fazer o login no sistema, exibindo a tela inicial

2. No botão login
    2.1 - irá verificar se existe na base de dados o email e a senha linculados.
    2.2 - se existir, deve enviar uma mensagem para o email informado, essa mensagem deve conter o código para verificar na tela de login.
    2.3 - Se estiver correto, deixa logar no sistema e vai exibir a tela inicial.
    2.4 - Se não existir o login e senha, irá exibir a mensagem "Usuário não identificado." e encerrar o fluxo de login.

## Tela Inicial
    1 - Cabeçalho: nome e foto do usuário à esquerda; botão Sair à direita.
    2 - Exibe mês/ano atual e totais das contas com somarTelaInicial ativo:
    Saldo Atual Disponível
        2.1 - Receitas (mês atual)
        2.2 - Despesas (mês atual)
        2.3 - Pago (despesas marcadas estaPago)
        2.4 - Pendente (despesas com estaPago = false)
        2.5 - Saldo Investido

    3 - Exibição das contas: imagem, saldo, saldo investido; ao clicar, mostra detalhes e lançamentos.
    4 - Exibição dos cartões: imagem, fatura atual, limite total; ao clicar, mostra detalhes e lançamentos.
    5 - Exibição de limites: ícone, nome, valor limite, valor gasto atual, porcentagem de uso com mudança de cor.
    6 - Rodapé: menu com acesso à tela inicial, lançamentos, balanço geral, perfil e cadastros.

## Lançamentos Feitos
    1 - Deve listar todos os lançamentos do usuário do mês atual, sendo possível escolher outros meses que tiver lançamento.
    2 - Filtro em grid horizontal por meses (futuro e passado), default no mês/ano atual. Como o default é o mês atual, vai mostra todas os lançamentos desta conta organizados de forma decrescente pela data, mostra primeiro do mês vigente, ou seja, se estamos no dia 22/08, então mostra os dados do dia 01/08 até 31/08. Ao usuário escolher outro mês, faz uma nova consulta pesquisando o dia 01 até o ultimo dia do mês que ele selecionou.
    3 - Filtros: Mês do lançamento, Geral, Conta, Cartão, Tipo Saldo (Saldo, SaldoInvestido, SaldoSimulado).
    4 - Rodapé fixo exibe balanço da conta selecionada.

## Balanço Geral
    Exibe gráficos:
        1 - Entradas, saídas e investidos
        2 - Gastos no cartão
        3 - Gastos por contas
        4 - Gastos por categoria e subcategoria
        5 - Receitas por categoria e subcategoria
        6 - Gastos de limites

## Perfil e Cadastro
    1 - Foto e nome do usuário no topo (sem cabeçalho), esta foto é escolhida pelo usuário e virá do gerenciamento de arquivos do dispositivo.
    2 - Opções:
        Editar Perfil
        Configurações: Modo Escuro, Moeda (R$ → U$), Mudar Senha (validação por e-mail)
        Resetar dados (apaga dados do banco, exceto usuário e dados automáticos)
    3 - Gestão de contas, cartões, categorias, subcategorias e limites com confirmação de exclusão via Swal.
    4 - Botão Sair da Conta realiza logout.
    5 - O usuário que tiver o atributo staff como true, e o cargo como ADM serão os administradores do sistema, esses terão funções a mais no sitema, a seguir relato as funcionalidades a mais.

## Funcionalidades para os usuários administradores
    1 - Deverá ter rotas e telas exclusivas para esses administradores.
        - Rotas para cadastrar imagens dentro do sistema sem está vinculados a alguma outra entidade/domain do sistema. Ao adicionar imagens o ADM poderá escolher o tipo da imagem, se é LOGO ou LANCAMENTO e esta imagem ficará solto no banco de dados, a espera de um vinculo.
    2 - Será possivel alterar o icones da categoria e sub categorias padrões do sistema, aqueles que não tem idUser registrado. 


## Relacionamentos

🔗 Relacionamentos entre Classes

| Entidade         | Relacionamento | Entidade Alvo           | Tipo | Observações                                                                                             |
| ---------------- | -------------- | ----------------------- | ---- | ------------------------------------------------------------------------------------------------------- |
| **User**         | possui         | Lancamento              | 1\:N | Um usuário pode criar múltiplos lançamentos.                                                            |
| **User**         | possui         | Conta                   | 1\:N | Um usuário pode ter várias contas (bancárias ou carteira).                                              |
| **User**         | possui         | Cartao                  | 1\:N | Um usuário pode ter vários cartões vinculados a suas contas.                                            |
| **User**         | possui         | Limite                  | 1\:N | Um usuário pode criar múltiplos limites vinculados a categorias.                                        |
| **User**         | possui         | Categoria               | 1\:N | Um usuário pode criar categorias personalizadas.                                                        |
| **User**         | possui         | SubCategoria            | 1\:N | Um usuário pode criar subcategorias personalizadas.                                                     |
| **Lancamento**   | pertence       | User                    | N:1  | Cada lançamento é criado por um único usuário.                                                          |
| **Lancamento**   | pertence       | Conta                   | N:1  | Cada lançamento (Receita, Despesa, Investimento, Transferência) está vinculado a uma conta de origem.   |
| **Lancamento**   | pertence       | Cartao                  | N:1  | Lançamentos que usam cartão podem estar vinculados a um cartão (opcional).                              |
| **Lancamento**   | pertence       | Categoria               | N:1  | Categoria opcional para o lançamento, usada para limites e relatórios.                                  |
| **Lancamento**   | pertence       | SubCategoria            | N:1  | Subcategoria opcional para detalhar o lançamento.                                                       |
| **Lancamento**   | possui         | Imagem                  | 1\:N | Um lançamento pode ter múltiplas imagens do tipo `LANCAMENTO`.                                          |
| **Conta**        | pertence       | User                    | N:1  | Cada conta pertence a um único usuário.                                                                 |
| **Conta**        | possui         | Lancamento              | 1\:N | Uma conta pode ter múltiplos lançamentos de receitas, despesas e transferências.                        |
| **Conta**        | possui         | Cartao                  | 1\:N | Uma conta pode ter múltiplos cartões vinculados.                                                        |
| **Conta**        | possui         | Imagem                  | 1:1  | Cada conta pode ter uma imagem do tipo `LOGO`.                                                          |
| **Cartao**       | pertence       | User                    | N:1  | Cada cartão pertence a um único usuário.                                                                |
| **Cartao**       | pertence       | Conta                   | N:1  | Cada cartão está vinculado a uma conta específica.                                                      |
| **Cartao**       | possui         | Lancamento              | 1\:N | Um cartão pode ter múltiplos lançamentos (crédito/débito).                                              |
| **Cartao**       | possui         | Imagem                  | 1:1  | Cada cartão pode ter uma imagem do tipo `LOGO`.                                                         |
| **Imagem**       | pertence       | Conta/Cartao/Lancamento | N:1  | Dependendo do tipo (`LOGO` ou `LANCAMENTO`), a imagem está vinculada a uma conta, cartão ou lançamento. |
| **Limite**       | pertence       | User                    | N:1  | Cada limite é criado por um usuário.                                                                    |
| **Limite**       | pertence       | Categoria               | N:1  | Cada limite é vinculado a uma categoria específica.                                                     |
| **Categoria**    | pertence       | User                    | N:1  | Um usuário pode criar categorias personalizadas.                                                        |
| **Categoria**    | possui         | SubCategoria            | 1\:N | Cada categoria pode ter múltiplas subcategorias.                                                        |
| **Categoria**    | possui         | Limite                  | 1:1 | Uma categoria pode ter um limite atribuídos ao usuário.                                         |
| **SubCategoria** | pertence       | Categoria               | N:1  | Cada subcategoria está vinculada a uma categoria específica.                                            |
| **SubCategoria** | pertence       | User                    | N:1  | Um usuário pode criar subcategorias personalizadas.                                                     |


✅ Notas importantes sobre relacionamentos e regras de integridade:

1. Exclusão em cascata:
    - Excluir um User deve excluir todas as contas, cartões, lançamentos, limites, categorias e subcategorias vinculadas a ele em idUser.
    - Excluir uma Conta deve excluir os lançamentos e cartões vinculados.
    - Excluir um Cartao deve excluir os lançamentos vinculados ao cartão.
    - Excluir uma Categoria ou SubCategoria só é permitido se não houver lançamentos vinculados e apenas será possivel excluir categorias e subscategorias que o usuario criou.

2. Chaves primárias e estrangeiras:
    - User.id → PK para todas as tabelas que possuem user_id.
    - Conta.id → PK; Lancamento.conta_id FK; Cartao.conta_id FK.
    - Cartao.id → PK; Lancamento.cartao_id FK.
    - Categoria.id → PK; SubCategoria.categoria_id FK; Limite.categoria_id FK.
    - SubCategoria.id → PK; Lancamento.subcategoria_id FK.
    - Imagem.id → PK; vincular via entidade_id + tipoImagem.

3. Relacionamentos importantes 1:N:
    - User → Conta
    - User → Cartao
    - User → Lancamento
    - User → Limite
    - User → Categoria
    - User → SubCategoria

4. Relacionamentos importantes N:1:
    - Lancamento → Conta
    - Lancamento → Cartao (opcional)
    - Lancamento → Categoria (opcional)
    - Lancamento → SubCategoria (opcional)

5. Relacionamentos opcionais:
    - Lancamento → Imagem (pode não ter imagens).
    - Conta → Imagem (pode não ter imagem).
    - Cartao → Imagem (pode não ter imagem).