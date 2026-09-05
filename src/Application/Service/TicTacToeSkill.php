<?php

declare(strict_types=1);

namespace Semitexa\TicTacToe\Application\Service;

use Semitexa\Llm\Attribute\AsAiSkill;
use Semitexa\Llm\Domain\Enum\AiArgumentPolicy;
use Semitexa\Llm\Domain\Enum\AiConfirmationMode;
use Semitexa\Llm\Domain\Enum\AiRiskLevel;

/**
 * The tic-tac-toe UI-skill: opens a small game board as a dialog (entry route
 * `/os/app/tictactoe`) where the user plays X against the assistant (O). Every
 * one of her moves is a real LLM call to the move endpoint — you are genuinely
 * playing against the model, not a canned bot.
 *
 * A leisure skill for Chill mode; the planner routes "let's play tic-tac-toe"
 * (any language) here.
 */
#[AsAiSkill(
    name: 'tic-tac-toe',
    summary: 'Play a game of tic-tac-toe against the assistant.',
    useWhen: 'The user wants to play tic-tac-toe / noughts and crosses / X and O, or "зіграти в хрестики-нолики" / "зіграймо в хрестики нолики" — anything asking to play that game.',
    avoidWhen: 'The user wants a different game or is not asking to play anything.',
    riskLevel: AiRiskLevel::Low,
    confirmation: AiConfirmationMode::Never,
    argumentPolicy: AiArgumentPolicy::None,
    channels: ['ui'],
    icon: 'gamepad-2',
    entry: '/os/app/tictactoe',
)]
final class TicTacToeSkill
{
}
