<?php

declare(strict_types=1);

namespace Semitexa\TicTacToe\Tests\Unit\Prompt;

use PHPUnit\Framework\TestCase;
use Semitexa\Prompt\Application\Service\PromptRegistry;
use Semitexa\Prompt\Application\Service\PromptRenderer;
use Semitexa\TicTacToe\Application\Prompt\TicTacToeOpponentPrompt;

final class TicTacToeOpponentPromptTest extends TestCase
{
    public function testIsByteIdenticalToTheLegacyInlineNowdoc(): void
    {
        $expected = <<<'PROMPT'
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

        $template = (new PromptRegistry())->buildFromClasses([TicTacToeOpponentPrompt::class])['tictactoe.opponent'];
        self::assertSame($expected, (new PromptRenderer())->renderTemplate($template)->system);
    }

    public function testRendersWithNoVariablesAndOnTheTictactoeChannel(): void
    {
        $template = (new PromptRegistry())->buildFromClasses([TicTacToeOpponentPrompt::class])['tictactoe.opponent'];

        self::assertSame([], $template->variableNames());
        self::assertSame('tictactoe', $template->channel);

        $rendered = (new PromptRenderer())->renderTemplate($template);
        self::assertStringContainsString('You are Semi, playing tic-tac-toe', $rendered->system);
        self::assertStringNotContainsString('{{', $rendered->system);
    }
}
