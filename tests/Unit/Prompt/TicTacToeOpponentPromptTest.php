<?php

declare(strict_types=1);

namespace Semitexa\TicTacToe\Tests\Unit\Prompt;

use PHPUnit\Framework\TestCase;
use Semitexa\Prompt\Application\Service\PromptRegistry;
use Semitexa\Prompt\Application\Service\PromptRenderer;
use Semitexa\TicTacToe\Application\Prompt\TicTacToeOpponentPrompt;

final class TicTacToeOpponentPromptTest extends TestCase
{
    /**
     * The template file is the whole prompt — nothing binds into it — so an
     * accidental edit is invisible until a model behaves oddly. This pins it.
     */
    public function testIsByteIdenticalToTheShippedTemplate(): void
    {
        $expected = <<<'PROMPT'
        You are the assistant of Semitexa OS, playing tic-tac-toe against a human. You are O; the human is X.
        Do not name yourself in the quip — the operator may have renamed you and the page shows your real name around it.
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

        // Render through the exact production path — a bound object passed to
        // render() — so this guards bound-object rendering, not just renderTemplate().
        self::assertSame($expected, (new PromptRenderer())->render(new TicTacToeOpponentPrompt())->system);
    }

    public function testRendersWithNoVariablesAndOnTheTictactoeChannel(): void
    {
        $template = (new PromptRegistry())->buildFromClasses([TicTacToeOpponentPrompt::class])['tictactoe.opponent'];

        self::assertSame([], $template->variableNames());
        self::assertSame('tictactoe', $template->channel);

        $rendered = (new PromptRenderer())->renderTemplate($template);
        self::assertStringContainsString('playing tic-tac-toe against a human', $rendered->system);
        self::assertStringNotContainsString('Semi,', $rendered->system);
        self::assertStringNotContainsString('{{', $rendered->system);
    }
}
