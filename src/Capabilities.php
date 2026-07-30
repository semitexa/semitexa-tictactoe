<?php

declare(strict_types=1);

namespace Semitexa\TicTacToe;

use Semitexa\Core\Attribute\Capability;

/**
 * What this package offers, for the capability catalog.
 *
 * The package ships no attributes of its own, so there is nothing for a
 * mechanism-level declaration to hang on — and without this the package is
 * invisible to anyone whose project has not installed it, which is precisely
 * the audience worth telling. The convention is one `Capabilities` class per
 * package: a definite place to look, and a definite place for a guard to check.
 *
 * Nothing reads this at runtime.
 */
#[Capability(
    id: 'os.tictactoe',
    summary: 'A tic-tac-toe UI-skill played against the assistant, where every opposing move is a real model call rather than a simulation.',
    useWhen: 'The environment needs a small, honest example of a leisure skill — one that exercises the skill loop instead of imitating it.',
    avoidWhen: 'Anything load-bearing. Every move costs a model call: that is the point of the demonstration, and the reason not to lean on it.',
    replaces: [
        'a client-side minimax opponent that never touches the skill loop',
        'a scripted game demo with no real turn taking behind the board',
    ],
)]
final class Capabilities
{
}
