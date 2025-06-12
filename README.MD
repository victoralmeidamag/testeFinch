# Teste Finch

Sistema de transferência feito em PHP puro utilizando arquitetura hexagonal e princípios SOLID.

---

## Como rodar o sistema

Siga os passos abaixo para executar o projeto localmente com Docker:

1. **Clone o repositório:**

   ```bash
   git clone https://github.com/seu-usuario/projeto-teste-finch.git

   cd testeFinch

2. **Copie o arquivo de Ambiente:**
    ```bash
    cp src/Config/.env.example src/Config/.env


> ⚠️ **Atenção:** Os próximos passos exigem Docker instalado.


3. **Rodar o comando**
    ```bash
    docker-compose up -d
    
    Para versões mais novas: docker compose up -d

4. **Assim que buildar o projeto, verificar o log do PHP para validar se o sistema está pronto para uso.
Rodar o comando:**
    ```bash
    docker logs php1

    A mensagem que precisa aparecer é a seguinte: "...Iniciando servidor PHP embutido"

##
## Como simular testes de carga

1. **Nessa etapa poderá ser usado POSTMAN, Insominia...**

    ```
    Acessar sua ferramente de preferência e fazer uma requisição do tipo POST, para a rota localhost/transfer.

    No header deverá ter as seguintes configurações:
    Content-Type: application/json

    No Body da requisição, deverá ser enviado 3 parâmetros obrigatórios:

    {
        "from": 1, // conta ja criada no migrate
        "to": 2, // conta ja criada no migrate
        "amount": 5.99 //valor que será transferido
    }
    
2. **Feita a requisição, deverá receber um retorno 200, com a seguinte mensagem:**
    ```bash
    {
    "success": true,
    "message": "Transferência realizada com sucesso"
    }
##
## Estratégias para evitar Deadlocks
1. **Arquivo src/Application/UseCase/TransferUseCase.php**

    ```
    No método execute, foi aplicada uma estratégia de ordenação dos bloqueios, baseada nos IDs das contas.
    Isso garante que todas as transações concorrentes bloqueiem os recursos sempre na mesma ordem (a conta com o menor ID primeiro), evitando que duas transações se "travem" ao acessar as mesmas contas em ordem invertida.

2. **Arquivo src/Adapters/Persistence/AccountRepositorySql.php**
    ```
    No método findAndLock, foi utilizado o WITH (UPDLOCK) nas consultas SQL.
    Esse comando instrui o SQL Server a aplicar um bloqueio de atualização já na leitura, impedindo que outras transações possam ler ou modificar o mesmo registro até que a transação atual seja finalizada. Isso ajuda a manter a consistência dos dados e evita conflitos entre transações simultâneas.
##
## Arquitetura utilizada, pensando em escalabilidade e manutenção

1. **Arquitetura Hexagonal. Por que?**
    ```
    Pensando em escalabilidade e possíveis mudanças tecnológicas ou novas implementações. Essa arquitetura permite que o código seja facilmente testável. E também não depende de banco de dados, protocolo externo... pois é um sistema desaclopado com camadas específicas para cada tipo de serviço.

2. **Explicação de como foi aplicado nesse sistema**
    1. **src/Domain. Onde contém a lógica principal do sistema.**

        ```        
        Entitities/Account.php -> Define o modelo
        Repositories/Account -> Define a interface de persistência
        Services/Transfer -> Define a regra de negócio pura
    
    2. **src/Application/UseCase** 
        ```
        Onde contém a lógica da aplicação, exemplo no TransferUseCase.php.
        Responsável por orquestrar o fluxo, chamando repositório e servico, sem precisar saber de onde os dados vem e vão.
    
    3. **src/Adapters**
        ```
        Aqui é onde faz a conexão entre serviços externos e o core do sistema.
        
        1 - Controller/TransferController.php -> Onde recebe a requisição HTTP e manda para o useCase.

        2 - Persistence/AccountRepositorySql.php -> Onde é feita a implementação da interface do repositório com SQL Server.

        3 - Database/SqlServerConnection.php -> Onde é feita a conexão com o banco de dados.

        4 - Http/DTO -> Onde é feita a organização de dados vinda da request.

        Esse tipo de implementação é útil pois, cada componente pode ser trocado sem haja impacto na lógica de domínio (Exemplo: Mudar de SQL Server para PostgreSql ou de API REST para GRAPHIQL)

3. Onde o SOLID foi utilizado?
    1. **S (Princípio da Responsabilidade única)**
        - Cada classe tem sua única responsabilidade.
    2. **O (Princípio do Aberto/Fechado)**
        - Novas features são adicionadas por alguma extensão sem alterar o código existente. Exemplo, se caso eu queira implementar uma serviço de mensagaria, como Rabbit seria possível criando esse novo adapter.
    3. **L (Substituição de Liskov)**
        - Os repositórios podem ser substituídos por mocks, caso seja necessário testes. Nesse caso não usamos herança, entre classe, somente abstrações, então o princípio pouco se aplica no nosso cenário
    4. **I (Segregação de Interface)**
        - Cada interface tem seus métodos bem estabelecidos, não gerando métodos que não serão usadas pelas classes que implementam essas interfaces
    5. **D (Inversão de Dependência)**
        - Foi utilizado bastante interface para que o domínio não depende de classes concretas, mas do uso de abstrações

## 
## Informações adicionais
1. **Script "stress_test.php"**

    1. Informações: 
        - Script feito para simular múltiplas requisições e para simular deadlocks.
        - Foi feito um log simples para registrar o tempo em que foi efetuada as requisições.
    2. Como rodar?
        - No Postman ou Insominia ou outro aplicativo que simule requisições, rodar em uma rota GET a url "localhost/stress_test.php"
        - Será gerado um arquivo com o log no caminho src/logs/stress_deadlock.log 

## 
## PROJETO DESENVOLVIDO POR VICTOR MAGALHAES PARA PROCESSO SELETIVO PARA A EMPRESA FINCH.