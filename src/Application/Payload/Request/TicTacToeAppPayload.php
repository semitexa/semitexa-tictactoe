<?php

declare(strict_types=1);

namespace Semitexa\TicTacToe\Application\Payload\Request;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Core\Contract\ValidatablePayloadInterface;
use Semitexa\Core\Http\Response\ResourceResponse;

/**
 * Entry route for the tic-tac-toe UI-skill — renders the standalone game board
 * hosted inside the game dialog in the OS.
 */
#[AsPublicPayload(
    path: '/os/app/tictactoe',
    methods: ['GET'],
    responseWith: ResourceResponse::class,
    produces: ['text/html'],
)]
final class TicTacToeAppPayload implements ValidatablePayloadInterface
{
    /**
     * @return array<string, list<string>>
     */
    public function validate(): array
    {
        return [];
    }
}
