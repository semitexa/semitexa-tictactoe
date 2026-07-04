<?php

declare(strict_types=1);

namespace Semitexa\TicTacToe\Application\Handler\PayloadHandler;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Http\Response\ResourceResponse;
use Semitexa\Llm\Application\Service\LlmProviderResolver;
use Semitexa\Llm\Application\Service\Planner;
use Semitexa\Llm\Domain\Model\LlmRequest;
use Semitexa\TicTacToe\Application\Payload\Request\TicTacToeMovePayload;

/**
 * Semi's move. The board is sent to the LLM with a focused system prompt asking
 * for a single JSON move; this is ONE completion (the planner is bypassed on
 * purpose — one ~140s call per move, not two). The model genuinely chooses the
 * move, so you play against Semi.
 *
 * Robustness: the returned index is validated against the live board (in range +
 * empty). On a bad/parse-failed/errored response it retries once, then falls
 * back to the first legal cell (`fallback: true`) so a wobbly model reply can
 * never break the game.
 */
#[AsPayloadHandler(payload: TicTacToeMovePayload::class, resource: ResourceResponse::class)]
final class TicTacToeMoveHandler implements TypedHandlerInterface
{
    private const WIN_LINES = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8],
        [0, 3, 6], [1, 4, 7], [2, 5, 8],
        [0, 4, 8], [2, 4, 6],
    ];

    #[InjectAsReadonly]
    protected LlmProviderResolver $providers;

    public function handle(TicTacToeMovePayload $payload, ResourceResponse $resource): ResourceResponse
    {
        $board = $payload->getBoard();
        $legal = $this->legalCells($board);

        // No move to make — board full or already won before Semi's turn.
        if ($legal === []) {
            return $this->json($resource, [
                'move' => null,
                'say' => '',
                'fallback' => false,
                'result' => $this->result($board),
            ]);
        }

        [$move, $say, $fallback] = $this->askSemi($board, $legal);

        $board[$move] = 'O';

        return $this->json($resource, [
            'move' => $move,
            'say' => $say,
            'fallback' => $fallback,
            'result' => $this->result($board),
        ]);
    }

    /**
     * @param list<string> $board
     * @param list<int>    $legal
     *
     * @return array{0:int,1:string,2:bool} [move, say, fallback]
     */
    private function askSemi(array $board, array $legal): array
    {
        $planner = new Planner();

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $response = $this->providers->provider()->complete(new LlmRequest(
                systemPrompt: $this->systemPrompt(),
                userMessage: $this->userMessage($board, $legal),
            ));

            if (!$response->success) {
                continue;
            }

            $decoded = $planner->extractJson($response->content);
            $move = is_array($decoded) && isset($decoded['move']) ? (int) $decoded['move'] : -1;

            if (in_array($move, $legal, true)) {
                $say = is_array($decoded) && isset($decoded['say']) ? trim((string) $decoded['say']) : '';

                return [$move, $this->cap($say), false];
            }
        }

        // Fallback: prefer centre, then a corner, else the first legal cell —
        // still a sensible move so the game keeps flowing.
        foreach ([4, 0, 2, 6, 8] as $preferred) {
            if (in_array($preferred, $legal, true)) {
                return [$preferred, '', true];
            }
        }

        return [$legal[0], '', true];
    }

    private function systemPrompt(): string
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

    /**
     * @param list<string> $board
     * @param list<int>    $legal
     */
    private function userMessage(array $board, array $legal): string
    {
        return (string) json_encode([
            'cells' => $board,
            'empty' => array_values($legal),
            'you' => 'O',
            'opponent' => 'X',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param list<string> $board
     *
     * @return list<int>
     */
    private function legalCells(array $board): array
    {
        // A finished board (someone already has a line) has no legal move.
        if ($this->winner($board) !== null) {
            return [];
        }

        $legal = [];
        foreach ($board as $i => $cell) {
            if ($cell === '') {
                $legal[] = $i;
            }
        }

        return $legal;
    }

    /**
     * @param list<string> $board
     *
     * @return array{status:string,winner:?string,line:?list<int>}
     */
    private function result(array $board): array
    {
        foreach (self::WIN_LINES as $line) {
            [$a, $b, $c] = $line;
            if ($board[$a] !== '' && $board[$a] === $board[$b] && $board[$b] === $board[$c]) {
                return ['status' => 'win', 'winner' => $board[$a], 'line' => $line];
            }
        }

        foreach ($board as $cell) {
            if ($cell === '') {
                return ['status' => 'continue', 'winner' => null, 'line' => null];
            }
        }

        return ['status' => 'draw', 'winner' => null, 'line' => null];
    }

    /**
     * @param list<string> $board
     */
    private function winner(array $board): ?string
    {
        $result = $this->result($board);

        return $result['status'] === 'win' ? $result['winner'] : null;
    }

    private function cap(string $say): string
    {
        $say = (string) preg_replace('/\s+/', ' ', $say);

        return mb_strlen($say) > 60 ? mb_substr($say, 0, 60) : $say;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function json(ResourceResponse $resource, array $data): ResourceResponse
    {
        return $resource
            ->setContent((string) json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))
            ->setHeader('Content-Type', 'application/json');
    }
}
