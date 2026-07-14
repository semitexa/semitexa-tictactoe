<?php

declare(strict_types=1);

namespace Semitexa\TicTacToe\Application\Prompt;

use Semitexa\Prompt\Attribute\AsPrompt;
use Semitexa\Prompt\Domain\Contract\BoundPromptInterface;

/**
 * Thin, self-binding prompt — body in resources/prompts/tictactoe.opponent.twig.
 */
#[AsPrompt(
    id: self::ID,
    channel: 'tictactoe',
    template: 'resources/prompts/tictactoe.opponent.twig',
    description: 'Semi playing tic-tac-toe as O; replies with a JSON move + short quip.',
)]
final class TicTacToeOpponentPrompt implements BoundPromptInterface
{
    public const ID = 'tictactoe.opponent';

    public function promptId(): string
    {
        return self::ID;
    }
}
