<?php

declare(strict_types=1);

namespace Semitexa\TicTacToe\Application\Payload\Request;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Core\Contract\ValidatablePayloadInterface;
use Semitexa\Core\Http\Response\ResourceResponse;

/**
 * Ask Semi (the LLM) for its next move. The client sends the current 9-cell
 * board; the handler runs one focused LLM completion and returns Semi's move.
 */
#[AsPublicPayload(
    path: '/os/app/tictactoe/move',
    methods: ['POST'],
    responseWith: ResourceResponse::class,
    consumes: ['application/json'],
    produces: ['application/json'],
)]
final class TicTacToeMovePayload implements ValidatablePayloadInterface
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
