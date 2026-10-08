# Semitexa Tic-Tac-Toe

`semitexa/tictactoe`

A tic-tac-toe game for Semitexa OS, played against the assistant. Each of the assistant's moves is a real LLM call, so it needs a working `semitexa/llm` backend.

## Install

Not included by the installer. Add it to an existing project from the project root:

```bash
docker compose run --rm --no-deps --user "$(id -u):$(id -g)" app composer require semitexa/tictactoe
bin/semitexa server:restart
```

It depends on `semitexa/os` (not in the installer's set); Composer installs it with it.

## What it provides

- The `tic-tac-toe` assistant skill, which opens the board dialog at `/os/app/tictactoe`.
- The move endpoint `/os/app/tictactoe/move` and the opponent prompt (`TicTacToeOpponentPrompt`, a `semitexa/prompt` template).

No console commands or tables.

## License

MIT, see [LICENSE](LICENSE).
