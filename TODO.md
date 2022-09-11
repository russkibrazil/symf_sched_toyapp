# Roadmap de Implementação

## Não planejado / agendado

- Definir carga dinâmica das *roles* de cada usuário
  - Há maneira de fazer o redirecionamento para a rota desejada depois da seleção do tipo de usuário?
- Criar *rule* de proprietário que pode gerenciar um ou mais estabelecimentos
  - Bloquear alteração dos seus privilégios
  - Alterações de Administradores devem ser autorizadas pelo proprietário quando afetam dados do estabelecimento
  - Ativar rotas do *Controller* Configuração: listar, editar
- Criar modelo de twig design para formulários (twig.yaml)
- Ao adicionar um funcionário a *Local_trabalho* ou *Funcionário*, verificar sua pré-existência para evitar redundância
- Refinar entrada de novos clientes e funcionarios
- https://critical-css.jandc.io/
- [Codificar melhor as cores de tema usando o atributo *data*](https://symfony.com/doc/current/frontend/encore/server-data.html)
- https://symfony.com/doc/current/controller/error_pages.html#testing-error-pages-during-development
- [Mexendo com is_granted](https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/security.html)
- As cores do texto do item de horários do funcionário estão em preto
- **Ações da LGPD**
- Refazer a PK de *HorarioTrabalho* e *EscalaTrabalho* (não permite mais de um turno no mesmo dia)
- https://symfony.com/doc/current/mailer.html#inlining-css-styles
- Quando em tela pequena, inserir boteões de ação numa nova linha
- Atualizar o index de agendamentos automaticamente periodicamente
- **Mailer**: [Free SMTP Server - Scalable Email Relay Service with Mailjet](https://www.mailjet.com/feature/smtp-relay/)
- Talvez fosse melhor levar a imagem de perifl para a parte superior e mostrar os serviços na parte inferior

### Ícones

https://material.io/resources/icons/?style=baseline
https://feathericons.com/
https://remixicon.com/ (Gostei)
https://icons.getbootstrap.com/

# Implementações não Planejadas 2021-02

- Melhorias visuais para mobile em diferentes renderizadores

# Implementações não planejadas 2020-12

- Toasts no index agendamentos
- Modal de pagamentos no index agendamentos
- Implementação de sistema de recuperação de senha
- Menu mobile
- Divisão do formulário de empresa em formulários menores (UX enhance)

# Implementações não planejadas 2020-11

- Adicionado o atributo *Conclusão esperada* no **Agendamento**.
- Implementado o [Feather Icons](https://feathericons.com/) em caráter de teste. Apresar de permitir o uso comercial e ser open source, a base de ícones é pequena.
- Atrubuto *Password* em **User** não é mais visto após a criação do usuário. Será criado um espaço específico para redefinir a senha do usuário. A senha de clientes é criada aleatoriamente pela (RandomLib)[https://github.com/paragonie/RandomLib].
- Atributo *CTPS* em **Funcionário** não é mais obrigatório
- Corrigidos os links do controller **Funcionário** que sempre levavam para a rota *Home*, mesmo se o usuário tiver o privilégio **ROLE_ADMIN**
- A apresentação do agendamento interativo foi melhorada para uso em telas de dispositivos mobile, principalmente [controlando quantos cards serão mostrados em cada linha](https://getbootstrap.com/docs/4.5/layout/grid/#row-columns)
- Inclusão do footer de direitos autorais
- Incluída a página de erro 404 personalizada

# Rodada de implementação 2020-10-00

- [x] Incluir CNPJ no fomr login
- [x] Incluir funcionalidade de login social / Open Authentication
- [x] Criptografar senha antes de salvar um novo usuário
- [x] Inserir CPF cliente no agendamento interativo
- [ ] Inserir histórico do cliente no cnpj ativo
- [x] Inserir atributo para explicação do serviço
- [x] Encontrar função para calcular relação fonte/cor fundo da aplicação
- [ ] Agendamento multi-loja para o cliente
  - [ ] Implementar esquema Matriz-Filial proposto
  - [ ] A escolha da loja deve ser o primiero passo do interativo
- [x] Incluir horários disponíveis no dia (Agendamento interativo)

## Notas Pessoais

### Agendamento multi-loja

O Agendamento multi-loja foi abandonado por hora. Por enquanto a solução do cliente optar por qual loja ele quer entrar via login é a escolhida.

### Login Social

Por hora, foi incluso somente o login Social do Facebook. Posso adicionar em breve outros servidores largamente utilizados, para economizar o trabalho

É importante pensar numa maneira de acelerar a entrada de usuários coletando informações direto do login social, mas por enquanto tenho problemas com o redirecionamento para o formulário de registro com informações pré-carregadas.

Outro ponto importante que tenho que prestar atenção logo é sobre a coleta de informações dos usuários e o atendimento de requisições neste sentido, baseado na LGPD (no mínimo) e GDPR (alvo).

Vale também dizer que já incluí o formulário de registro de usuários.

### CNPJ no login

O CNPJ foi injetado diretamente na sessão. Talvez em breve não será mais necessário o serviço *GerenteSessao*.

### Sitema de cores

 (https://juniorrocha.com.br/2017/06/harmonia-de-cores-a-matematica-da-combinacao-de-cores/3/)
 https://www.w3schools.com/colors/colors_complementary.asp
 http://www.squids.com.br/arduino/index.php/tabelas/105-converter-cores-rgb-em-html

# Rodada de implementação 2020-09-01

- [x] Incluir helpers https://symfony.com/doc/current/reference/forms/types/text.html#help
- [x] Flash messages ao salvar modificações num controller
- [x] Criar hierarquia de privilégios
- [x] Rever roteamento do *AgendamentoServicos*
- [x] Recriar as rotas do *Configuracao*
  - [ ] **Index** lista das empresas que o usuário é proprietário
  - [x] **Editar** Edita as informações da loja selecionada
  - [ ] **Novo** ainda indisponível
  - [ ] **Remover** ainda indisponível
- [x] Criar campos de salário e comissão em *Local Trabalho*
- [x] Inserir campo de duração em *Serviço*
- [x] Rever as classes a incluir diretamente na tag do formulário https://symfony.com/doc/current/reference/forms/types/text.html#attr
- [x] Reescrever a inclusão de clientes e funcionários

## Notas pessoais

### Flash messages

Somente inseri as mensagem de sucesso. Preciso ter mais atenção às mensagens de aviso, erro e informação (?)

### Carga dinâmica de roles

Aaaaah carai, finalmente! A saída foi interferir em como os resultados eram coletados do banco, usando o *Repository*. Não creio que foi a saída mais elegante, mas vai me comprar um tempo até eu descobrir o que realmente fazer.

Me parece que os *voters* são os caras mais adequados à tarefa, já que os *guards* são adequados para credenciais do tipo senha ou token, ou seja, validação de credenciais, não de nível de acesso.

# Rodada de Implementação 2020-09-00

- [x] Definir carga dinâmica das *roles* de cada usuário
- [ ] Criar *rule* de proprietário que pode gerenciar um ou mais estabelecimentos
  - [ ] Bloquear alteração dos seus privilégios
  - [ ] Alterações de Administradores devem ser autorizadas pelo proprietário quando afetam dados do estabelecimento
  - [ ] Ativar rotas do *Controller* Configuração: listar, editar
- [ ] Criar modelo de twig design para formulários (twig.yaml)
- [x] Atualizar embedded JS do formulário de Agendamento para JQUERY
- [x] Terminar migração para entidade *User*
- [x] Criar seletor de estabelecimento ao entrar (dá para fazer a troca durante o uso)
- [x] Programar a exclusão de nodes órfãos nas tabelas nestadas ([Veja aqui](**2020-08-00 Teste de CU**))
  - [x] Agendamento - AgendamentoServicos
  - [x] Funcionario - HorarioTrabalho
  - [x] Configuração - EscalaTrabalho
- [ ] Ao adicionar um funcionário a *Local_trabalho* ou *Funcionário*, verificar sua pré-existência para evitar redundância

## Notas Pessoais

**Esta lista foi abortada devido a atrasos no desenvolvimento do esquema de segurança**

### Pré-existência

Devido as mudanças para acomondar o sistema de segurança e login, a lógica de inclusão de funcionários e clientes deve ser revista

### Roles

~~Foi relativamente simples criar o seletor de *role*, o problema reside em carregar dinamicamente depois de selecionada a categoria de usuário. É necessára a implementação de *Voters* para fazer tal trabalho adequadamente, além de ser uma boa prática.~~

https://symfony.com/doc/current/security.html#security-role-hierarchy

https://symfony.com/doc/current/security/voters.html

https://github.com/symfony/symfony/issues/12025

https://github.com/symfony/symfony/issues/12025#issuecomment-219726353

~~https://stackoverflow.com/questions/31986324/symfony2-authentication-roles-not-updating-in-twig-template~~

https://stovepipe.systems/post/symfony-security-roles-vs-voters

https://symfony.com/doc/current/security/guard_authentication.html

https://symfony.com/doc/current/components/security/authorization.html#voters

**Potenciais soluções**

~~https://stackoverflow.com/questions/59843857/how-to-update-roles-in-security-token-in-symfony-4-without-re-logging-in~~

~~https://stackoverflow.com/questions/63556903/symfony-user-logout-after-role-change~~

~~Por hora, não consegui implementar nenhuma solução. Minha saída rápida é alterar as permissões no banco e dizer que a PK de User é [CPF, EMAIL].~~

**É necessário carregar previamente o CNPJ**. Como opcional, podemos colocar o *tipo de usuário que fará login* e armazenar num campo invisível no formulário

1. https://symfony.com/doc/current/security/user_provider.html#using-a-custom-query-to-load-the-user
2. https://symfony.com/doc/current/security/user_provider.html#chain-user-provider

Talvez a última esperança antes de montar o firewall: https://symfony.com/doc/master/security/user_provider.html#using-a-custom-query-to-load-the-user
https://symfony.com/doc/master/security/user_provider.html#creating-a-custom-user-provider

Tentativa 1: Carregar as roles usando query direto do repositório da entidade

Tentativa 2: Criar entidades separadas que cuidem de tipos diferentes de usuário (como gerenciar as *roles*?)

Tentativa 3: Veja a thread do repositório do Symfony anotada acima

# Rodada de Implementação 2020-08-00

- [x] Testar *Create & Update* em todos os formulários
- [x] Implementar sistema de segurança Symfony
  - [x] Adicionar o cliente ativo no formulário interativo
  - [x] Criar dinamicamente os menus de acesso baseado no perfil do usuário
- [x] Implementar estética inicial

## Notas Pessoais

### Estética

Bom, se mostrou uma boa prática usar o *Webpack Encore*, conforme sugerido. Contudo, tive que usar a versão *classic* do Yarn para poder compilar adequadamente os arquivos.

Veja aqui sobre o problema: https://stackoverflow.com/questions/63755865/error-cannot-find-module-babel-plugin-syntax-dynamic-import-in-symfony-proje

Motivo: adicionar manualmente os arquivos faz com que se perca as vantagens do versionamento dos pacotes incluídos por composer, npm e outros gerenciadores...
No fritar dos ovos, fico com *Bootstrap* como folha de estilos, já que muitos dos componentes são gerados automaticamente utilizando o referido. Quem sabe não tenho mais sorte com o Laraval da próxima vez (ou quando eu decidir mudar de framework)

### Implementação do sistema de segurança

Algumas anotações pertinentes para as próximas rodadas de implementação:

- Armazenar as *permission rules* na tabela de Cliente/Funcionario, já que os privilégios podem mudar dependendo de que momento o usuário entrar no sistema. Em outras palavras, ele pode ser um cliente num momento, mas noutro ele pode agir como um funcionário, desde que este respeite seu horário de trabalho (e por acaso eu vou implementar essa restrição? Creio que sim, mas com restrições: talvez seja interessante que o usuário funcionário acesse funcionalidades não críticas, como visualização de agenda, quando estiver fora do seu horário de trabalho).
- Se houver perfil em ambas as tabelas, permitir que ele escolha qual será a sua visão, baseado nas suas agendas de trabalho
- Caso esteja em horário de trabalho, não é possível escolher qual a visão de empresa
- Revisar o esquema de chave primária de **User** caso seja mais conveniente deixar que o mesmo CPF seja vinculado a mais de um e-mail

### Teste de CU

Quando há um *nested form*, a operação de remoção de itens não é persistida. *Ver sobre* **orphanRemoval** *do Doctrine*. É importante relembrar que os métodos de remoção dentro das entidades "maiores" estão incompletos, ou seja, somente removem os itens das suas listas internas.

# Rodada de Implementação 2020-07-00

- [x] Realmente integrar os privilégios do funcionário no formulário do referido
  - [ ] Testar a inclusão na tabela *Local_trabalho*
- [x] Listagem de funcionários não é listada corretamente
  - [x] Funcionario_index
  - [x] Agendamento
- [ ] Criação interativa de agendamento
  - [x] Criar efeitos entre os passos
  - [x] Criar visão de resumo antes da confirmação
  - [ ] Coletar os dados e inserir adequadamente

## Notas Pessoais

### Integração de privilégios do funcionário

Convém mencionar que é preciso analisar se o CPF citado já existe no BD antes de tentar verificar se o formulário é válido

### Listagem inadequada de funcionários

Faltou somente dizer ao Doctrine que o **CPF_Funcionario** de *LocalTrabalho* ainda era uma ID.

# Rodada de implementação **2020-05-00**

- [x] Coletar CNPJ dos dados de sessão
  - [x] Cliente
  - [x] Funcionário
  - [x] Serviço
  - [x] Agendamento
- [x] Criar formulário de *funcionário* mesclando dados e privilégios dentro da empresa ativa
- [x] Criação interativa de agendamento
  - [x] Pré-carregar funcionários e suas respectivas imagens
  - [x] Pré-carregar serviços e suas respectivas imagens
- [x] Pré-carga de comboboxes
  - [x] Serviço
  - [x] Funcionário
- [x] Modificar métodos de exclusão
  - [x] Funcionário
  - [ ] Cliente
  - [x] Serviço
- [x] Melhorar a inclusão de múltiplos serviços em um agendamento

## Notas pessoais

### Carga de comboboxes

#### Quando há potencialmente pouco a se buscar

A ideia de fazer a busca é parecida com a do curso de Angular que fiz: Busco os  funcionarios previamente e deixo à disposição para uma caixa de busca procurar o código conforme o funcionário selecionado.

#### Quando não é previsível, mas tende a muitos registros

Como fazer com o cliente, para o caso do atendente? Fico refém de uma busca por palavra chave [ou CPF, que facilita bastante o serviço]. Talvez, quando vendo a ficha do cliente, o atendente já possa começar a criar um atendimento direto por lá!

### Criar formulário mesclado de funcionário

A primeira saída parece ser: criar uma nova classe mesclando as duas previamente existentes. Fazer a carga inicial e o salvamento pode ser um problema, já que as informações terão de ser dissociadas manualmente, mas facilita na hora de criar o formulário

### Métodos de exclusão

Visando consistência, não é possível simplesmente sumir com um registro crítico do BD. Enquanto o SGBD não cuida adequadamente desta tarefa, faremos o bloqueio via software

### Formulário de funcionário

A melhor saída por hora é redirecionar o usuário. Estou sem tempo para fazer bonito no momento, mas temos a funcionalidade de maneira separada. Sei que não é a melhor saída, mas é a que posso entregar por hora.

### Agendamentos Interativos

Tive alguns problemas:

1. Como criar de fato os agendamentos, sabendo que eu preciso saber quais os serviços que o usuário deseja para então poder calcular de maneira inteligente quais os horários disponíveis? No atual modelo, sou obrigado a primeiro criar um agendamento e depois proceder para os serviços. Isso também é válido para o form de agendamento comum.
2. Como vou fazer a transição suavemente em JS para poder coletar as informações gerar e depois salvar no banco? *Hidden forms?* jQuery?
3. Caso o usuário precise, a edição das informações do agendamento também serão por esse formato interativo?
4. Fazer com que o clique do usuário, equalquer ponto das imagens do funcionário ou no item que representa o serviço selecionem natualmente o item

### Métodos diferenciados de exclusão

É importante que assim que o usuário administrador de uma determinada empresa tente inserir um serviço ou funcionário que já existiu em um dado tempo no passado, o sistema traga o registro antigo, avisando (obviamente) de que é uma recuperação de informações pré existentes.

Para o caso do funcionário, há umn destaque especial para problemas de auditoria para caso um funcionário seja removido da base.

De maneira objetiva, se o funcionário não tem nenhuma movimentação, ele pode ser naturalmente ser "apagado" sem afetar nenhum movimento da emrpesa ativa. Contudo, isso somente pode ser feito caso não haja nenhum contrato ativo comprovando os laços do funcionáio com a empresa, mesmo que no sistema não conste nenhum registro ou movimentação do referido.

Quanto ao cliente, não faz sentido apaga-lo, a menos que seja requerido o não processamento de informações daquele usuário ou algum outro caso que não vem à mente agora.

### Inclusão de múltiplos servicos

Bom, foi feito na marra por JS puro. Suicida, mas é o que preciso por hora. Preciso testar agora a coleta das informações. Está esteticamente péssimo, mas visualmente é o que preciso.
