<?php

declare(strict_types=1);

namespace Semitexa\TicTacToe\Application\Prompt;

use Semitexa\Prompt\Attribute\AsPrompt;

/**
 * Semi's tic-tac-toe opponent system prompt. Thin definition — the body lives in
 * `resources/prompts/tictactoe.opponent.twig`.
 */
#[AsPrompt(
    id: self::ID,
    channel: 'tictactoe',
    template: 'tictactoe.opponent.twig',
    description: 'Semi playing tic-tac-toe as O; replies with a JSON move + short quip.',
)]
final class TicTacToeOpponentPrompt
{
    public const ID = 'tictactoe.opponent';
}
