# 📌 Descrição das Classes e Requisitos do Sistema

# 🧑 User (Usuários)

Responsável pelo cadastro e login no sistema utilizando e-mail e senha.
- Todos os lançamentos e cadastros no sistemas estarão ligados ao usuário.

## Regras
- O par **e-mail + senha** deve ser único.

## Cache
- O usuário autenticado deve permanecer salvo em cache/local storage para evitar necessidade de login frequente no aplicativo.

---

# 💸 Lançamentos

Responsável por gerenciar todas as movimentações financeiras do sistema.

## Tipos de lançamento
- Receita
- Despesa
- Transferência
- Investimento

## Fluxo de criação
- O usuário acessa a tela de lançamento de duas formar
1. Através de um botão `"+"` que ficará flutuante na parte de baixo da tela no canto direito.
2. Através do menu lateral.

---

## 💰 Receitas

### Regras
- Deve exigir uma conta de destino.
- O saldo da conta selecionada será atualizado.

---

## 📉 Despesas

### Regras
- Deve exigir uma conta de origem do dinheiro.
- Remove valor do `saldo` da conta selecionada.

---

## 💳 Cartão Vinculado

Ao selecionar um cartão no lançamento: 

### Débito
- O valor será removido do campo `saldo` da conta vinculada ao cartão, pois significa uma compra no débito.

### Crédito
- O valor será somado ao campo `faturaTotal` do cartão.

- Deve permitir parcelamento:
  - Caso não exista parcelamento → considerar `"à vista"`;
  - Caso exista parcelamento → gerar múltiplos lançamentos automaticamente.

### Parcelamento
Exemplo:
- Valor: `R$ 150,00`
- Parcelamento: `3x`

Resultado:
- Parcela 1 → data atual → `parcelaAtual = 1`
- Parcela 2 → +30 dias → `parcelaAtual = 2`
- Parcela 3 → +60 dias → `parcelaAtual = 3`

---

## 📅 Data do Lançamento

### Regras
- A data padrão deve ser a data atual.
- O usuário poderá alterar a data para o passado.

---

## 📈 Investimentos
Uma instancia de contas

### Regras
- Deve exigir conta de origem (de onde veio o dinheiro para investir).
- Deve perguntar se a operação será:
  - `Investir`
  - `Resgatar`

### Investir
- Adiciona valor em `saldoInvestido`

### Resgatar
- Remove valor de `saldoInvestido`
- Adiciona valor em `saldo`

---

## 🔄 Transferências

### Regras
- Deve exigir:
  - Conta de origem
  - Conta de destino

### Funcionamento
- Remove valor do `saldo` da conta origem;
- Adiciona valor ao `saldo` da conta destino.

### Transferência para investimentos
Deve existir opção:
- `"Enviar para saldo investidor"`
Signfica que investirar no mesmo banco que tem a conta.

Nesse caso:
- O valor sai do `saldo`;
- O valor entra em `saldoInvestido`.

---

## ⚙️ Regras Gerais de Lançamentos

### Exclusão
Ao excluir um lançamento:
- Todas as movimentações financeiras devem ser revertidas.

### Edição
- Apenas Conta origem, destino, valor, data e descrição poderá ser alterado;
- O saldo deve ser atualizado.

### Categorias
- Categoria é opcional;
- Caso seja selecionada:
  - Atualizar `valorGastoAtual` da entidade `Limites` daquela categoria utilizada.

### Imagens
- O usuário pode anexar comprovantes.
- As imagens devem ser armazenadas na entidade `Imagem`.
- O tipo da imagem será:
  - `LANCAMENTO`

---

# 🏦 Contas

Representam bancos ou dinheiro físico.

## Campos
- `carteira`
  - Booleano indicando dinheiro físico.
- `saldo`
  - Saldo disponível.
- `saldoInvestido`
  - Valor investido.
- `nome`
  - Nome da conta.
- `imagem`
  - Selecionada no banco de imagens (`tipoImagem = LOGO`).
- `somarTelaInicial`
  - Define se participa dos totais da tela inicial.

---

# 💳 Cartões

Representam cartões:
- Crédito
- Débito
- Múltiplo

## Regras
- `faturaAtual` deve ser atualizada automaticamente ao lançar algo para o castão de credito.
- Débito remove valor diretamente do saldo da conta vinculada.
- Imagem selecionada:
  - `tipoImagem = LOGO`

---

# 🖼️ Imagem

Responsável pelo gerenciamento de imagens do sistema.

## Campos
- `caminhoImagem`
  - Caminho físico da imagem.
- `tipoImagem`
  - Pode ser:
    - `LOGO`
    - `LANCAMENTO`

---

# 🚨 Limites

Responsável pelo controle de gastos por categoria.

## Regras
- Cada limite pertence a uma categoria.
- Deve possuir:
  - `valorLimite`
  - `valorGastoAtual`

## Atualização
- Toda despesa atualiza `valorGastoAtual`.

## Indicadores visuais
- Menor que 70% → Verde
- Entre 70% e 99% → Amarelo
- Maior ou igual a 100% → Vermelho

## Reset Mensal
- Todo dia 01:
  - `valorGastoAtual = 0`
- Sem apagar histórico.

---

# 🗂️ Categorias

Representam agrupamentos financeiros.

## Tipos
- Despesas
- Créditos
- Investimentos

## Campos
- Nome
- Cor
- Ícone (FontAwesome)
- `ativo`

## Regras
- Se `ativo = false`, não pode ser utilizada nos lançamentos.

## Seeders
Categorias padrão:
- Academia
- Alimentação
- Educação
- Moradia
- Saúde
- Transporte
- Investimentos
- Salário
- Entre outras.

---

# 📁 SubCategorias

Detalham ainda mais uma categoria.

## Campos
- Nome
- Cor
- Ícone
- `ativo`

## Regras
- Pertencem a uma categoria.
- Se `ativo = false`, não aparecem nas seleções durante o lançamento.

## Seeders
Exemplos:
- Padaria (Mercado)
- Delivery (Alimentação)
- IPTU (Impostos)
- Seguro de Vida (Seguros)

---

# 🏠 Tela Inicial

Tela exibida após login.

## Cabeçalho
- Foto do usuário;
- Nome do usuário;
- Botão `"Sair"`.

---

## 📊 Resumo Financeiro

Cards:
1. Saldo de todas as contas disponível
2. Receitas do mês atual
3. Despesas do mês atual
4. Saldo Investido 

---

## 🏦 Contas
Exibir:
- Imagem
- Saldo
- Saldo Investido

Ao clicar:
- Abrir detalhes da conta;
- Exibir lançamentos filtrados por mês atual.

---

## 💳 Cartões
Exibir:
- Imagem
- Fatura Atual
- Limite Total

Ao clicar:
- Abrir detalhes;
- Exibir lançamentos relacionados.

---

## 🚨 Limites
Exibir:
- Ícone
- Categoria
- Valor limite
- Valor gasto
- Barra percentual colorida

---

# 📱 Menu Lateral 

Menu:

1. Tela Inicial
2. Lançamentos
3. Novo Lançamento
4. Balanço Geral
5. Perfil e Cadastros

---

# 📋 Lançamentos Feitos

## Funcionalidades
- Exibir lançamentos por mês/ano.
- Filtro horizontal entre meses.

## Filtros
- Geral
- Conta
- Cartão
- Mês/ano
- Tipo de saldo:
  - saldo
  - saldoInvestido
  - saldoSimulado

## Rodapé
Na parte inferior da tela, deverá ser sempre visível, mesmo com rolagem dos lançamentos
- Balanço:
  - Gastos - Recebimentos
- Expansível para mostrar totais.

---

# 📈 Balanço Geral

## Gráficos
- Entradas x Saídas
- Receitas
- Despesas
- Investimentos
- Gastos em cartões
- Gastos por categoria
- Gastos por subcategoria
- Limites atingidos

---

# 👤 Perfil e Cadastros

## Informações
- Foto do usuário;
- Nome do usuário.

---

## Funcionalidades

### Editar Perfil

### Configurações
- Modo escuro;
- Alteração de moeda (`R$ → US$`);
- Alteração de senha;
- Reset de dados.

### Minhas Contas
CRUD completo:
- Criar
- Editar
- Excluir

Ao excluir:
- Confirmar via modal/swalert;
- Excluir também lançamentos relacionados.

### Meus Cartões
CRUD completo com confirmação.

### Minhas Categorias e SubCategorias
CRUD completo:
- Permitir desativação;
- Não permitir exclusão se estiver vinculada a lançamentos.

### Meus Limites
CRUD completo vinculado à categoria.

### Logout
- Opção `"Sair da Conta"`.

```