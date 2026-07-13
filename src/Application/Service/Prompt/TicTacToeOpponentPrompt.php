<?php

declare(strict_types=1);

namespace Semitexa\TicTacToe\Application\Service\Prompt;

use Semitexa\Prompt\Attribute\AsPrompt;
use Semitexa\Prompt\Domain\Contract\PromptDefinitionInterface;

/**
 * Semi's tic-tac-toe opponent system prompt. Migrated out of the inline nowdoc
 * in {@see \Semitexa\TicTacToe\Application\Handler\PayloadHandler\TicTacToeMoveHandler}::systemPrompt().
 *
 * No variables — the board state and legal moves are the user message.
 */
#[AsPrompt(
    id: self::ID,
    channel: 'tictactoe',
    description: 'Semi playing tic-tac-toe as O; replies with a JSON move + short quip.',
)]
final class TicTacToeOpponentPrompt implements PromptDefinitionInterface
{
    public const ID = 'tictactoe.opponent';

    public function system(): string
    {
        return <<<'PROMPT'
        You are Semi, playing tic-tac-toe against a human. You are O; the human is X.
        The board is 9 cells, indices 0-8, laid out:
         0 | 1 | 2
         3 | 4 | 5
         6 | 7 | 8
        You will be given the current cells (values "X", "O" or "" for empty) and the
        list of empty indices you may play. Choose the best move for O: win if you can,
        otherwise block X, otherwise take the strongest square.
        Reply with ONLY JSON, no prose, no code fences:
        {"move": <index of an EMPTY cell you chose>, "say": "<a short, friendly ≤6-word quip>"}
        PROMPT;
    }
}
