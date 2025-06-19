# E-Chruch-Backend - Ambiente Docker

Este projeto utiliza Docker e Docker Compose para facilitar o desenvolvimento e execução do ambiente Laravel + Node.js.

## Pré-requisitos

- [Docker](https://www.docker.com/get-started)
- [Docker Compose](https://docs.docker.com/compose/)

## Subindo o ambiente

Para iniciar todos os serviços necessários, execute:

```sh
docker compose up -d --build
```

- O parâmetro `--build` garante que as imagens serão reconstruídas caso haja alterações no código ou nas dependências.
- O parâmetro `-d` executa os containers em segundo plano (modo "detached").

## Acessando a aplicação

Após subir os containers, acesse a aplicação pelo navegador em:

```
http://localhost:8000
```

> O endereço pode variar conforme a configuração do seu [nginx.conf](nginx.conf) ou portas expostas no [docker-compose.yml](docker-compose.yml).

## Comandos úteis

- Parar os containers:
  ```sh
  docker compose down
  ```
- Ver logs dos containers:
  ```sh
  docker compose logs -f
  ```
- Executar comandos dentro do container:
  ```sh
  docker compose exec app bash
  ```

## Estrutura

- O código-fonte da aplicação está em [`application/`](application/README.md).
- O Dockerfile principal está em [`Dockerfile`](Dockerfile).
- O arquivo de configuração do Docker Compose está em [`docker-compose.yml`](docker-compose.yml).

---

Para mais detalhes sobre a aplicação, consulte o [`application/README.md`](application/README.md).