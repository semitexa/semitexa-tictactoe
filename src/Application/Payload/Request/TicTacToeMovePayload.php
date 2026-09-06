<?php

declare(strict_types=1);

namespace Semitexa\TicTacToe\Application\Payload\Request;

use Semitexa\Authorization\Attribute\AsProtectedPayload;
use Semitexa\Os\Domain\Contract\OsSurfacePayloadInterface;
use Semitexa\Core\Contract\ValidatablePayloadInterface;
use Semitexa\Core\Http\Response\ResourceResponse;

/**
 * Ask the assistant (the LLM) for her next move. The client sends the current
 * 9-cell board; the handler runs one focused LLM completion and returns her move.
 */
/**
 * Console surface: gated by OsAdminGate, not merely by being signed in.
 *
 * This window mounts under /os/app, so a visitor authenticated by the host
 * site's own login would satisfy #[AsProtectedPayload] exactly as an operator
 * does. OsSurfacePayloadInterface is what asks the narrower question.
 */
#[AsProtectedPayload(
    path: '/os/app/tictactoe/move',
    methods: ['POST'],
    responseWith: ResourceResponse::class,
    consumes: ['application/json'],
    produces: ['application/json'],
)]
final class TicTacToeMovePayload implements ValidatablePayloadInterface, OsSurfacePayloadInterface
{
    /** @var list<string> nine cells, each '', 'X' or 'O' */
    private array $board = [];

    /**
     * @return array<string, list<string>>
     */
    public function validate(): array
    {
        $errors = [];
        if (count($this->board) !== 9) {
            $errors['board'] = ['A board of exactly 9 cells is required.'];
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    public function getBoard(): array
    {
        return $this->board;
    }

    /**
     * @param array<int|string, mixed> $board
     */
    public function setBoard(array $board): void
    {
        $cells = [];
        foreach ($board as $cell) {
            $value = is_string($cell) ? strtoupper(trim($cell)) : '';
            $cells[] = ($value === 'X' || $value === 'O') ? $value : '';
        }

        $this->board = $cells;
    }
}
