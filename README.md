📌 Funcionalidades

  Rotas funcionais para jogar Super Trunfo.

  Criação de Usuarios/Adm

  Criação de Cards/Decks

  Comparação automática de atributos entre cartas.

  Funcionalidade de Partida

🚀 Tecnologias Utilizadas

  PHP  

  Token JWT

📍 Rotas da Aplicação


🔑 Autenticação

  Login de Usuário

  POST /login - Realiza a autenticação do usuário.

🔧 Administração

📇 Cartas

  POST /adm/cards/ - Cria uma nova carta.

  GET /adm/cards/ - Retorna todas as cartas.

  PUT /adm/cards/{id} - Atualiza uma carta pelo ID.

  GET /adm/cards/{id} - Retorna os detalhes de uma carta pelo ID.

  DELETE /adm/cards/{id} - Remove uma carta pelo ID.

🃏 Decks

  POST /adm/deck/ - Cria um novo deck.

  GET /adm/deck/ - Retorna todos os decks.

  PUT /adm/deck/{id} - Atualiza um deck pelo ID.
  
  GET /adm/deck/{id} - Retorna os detalhes de um deck pelo ID.
  
  DELETE /adm/deck/{id} - Remove um deck pelo ID.

👤 Usuários

  POST /adm/user/ - Cria um novo usuário.
  
  GET /adm/user/ - Retorna todos os usuários.
  
  GET /adm/user/checkAdmin/{id} - Verifica se um usuário é administrador.
  
  PUT /adm/user/{id} - Atualiza um usuário pelo ID.
  
  GET /adm/user/{id} - Retorna os detalhes de um usuário pelo ID.
  
  DELETE /adm/user/{id} - Remove um usuário pelo ID.

🎮 Jogador

🕹️ Partidas

  POST /player/games/ - Cria uma nova partida.
  
  GET /player/games/{session_id} - Retorna uma partida criada pelo ID da sessão.
  
  GET /player/games/ - Retorna todas as partidas criadas.
  
  PUT /player/games/joinGame/{session_id} - Adiciona um jogador a uma partida.
  
  PUT /player/games/startGame/{session_id} - Inicia uma partida.
  
  GET /player/games/getFirstCards/{session_id} - Retorna as primeiras cartas de uma partida.
  
  POST /player/games/compareCards/{session_id} - Compara cartas durante a partida.
  
  GET /player/games/gameInformation/{session_id} - Retorna informações da partida.


📥 Instalação e Execução

  Clone o repositório:
  
  git clone https://github.com/LeoGBarros/SuperTrunfo.git
  
  Acesse a pasta do projeto:
  
  cd SuperTrunfo
  
  Execute o projeto e teste as funcionalidades no POSTMAN ou INSOMNIA:

📧 Contato

  Caso tenha dúvidas ou sugestões, entre em contato:
  
  GitHub: LeoGBarros
