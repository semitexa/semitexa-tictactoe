<?php

declare(strict_types=1);

namespace Semitexa\TicTacToe\Application\Handler\PayloadHandler;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Http\CspNonce;
use Semitexa\Core\Http\Response\ResourceResponse;
use Semitexa\Ssr\Application\Service\Asset\AssetManager;
use Semitexa\TicTacToe\Application\Payload\Request\TicTacToeAppPayload;

/**
 * Renders the tic-tac-toe board — a self-contained dark-themed page embedded as
 * a dialog by the OS. You are X; each of the assistant's (O) moves posts the
 * board to `/os/app/tictactoe/move`, which runs a real LLM completion. Tinted
 * to the OS dark palette via `--ui-*` overrides (same approach as the calendar
 * dialog).
 *
 * Her name is not baked in: this package does not depend on semitexa/os, and
 * the name is renameable anyway, so the page asks /os/preferences for it the
 * same way it asks for the theme. Rename her and the game follows.
 */
#[AsPayloadHandler(payload: TicTacToeAppPayload::class, resource: ResourceResponse::class)]
final class TicTacToeAppHandler implements TypedHandlerInterface
{
    public function handle(TicTacToeAppPayload $payload, ResourceResponse $resource): ResourceResponse
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tic-tac-toe · Solomiia</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    color-scheme: dark;
    --font: 'IBM Plex Sans', system-ui, sans-serif;
    --text: #eaf2ff;
    --muted: #8d9bb8;
    --page: #0c1020;
    --panel: #0f172a;
    --line: rgba(148,163,184,.18);
    --accent: #a78bfa;
    --accent-soft: rgba(167,139,250,.16);
    --x: #37b7ff;
    --o: #a78bfa;
  }
  * { box-sizing: border-box; }
  html, body { margin: 0; height: 100%; background: var(--page); }
  body { font-family: var(--font); color: var(--text); display: flex; align-items: center; justify-content: center; padding: 18px; }
  .ttt { width: 100%; max-width: 360px; display: flex; flex-direction: column; gap: 16px; }
  .ttt__head { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; }
  .ttt__title { font-weight: 700; font-size: 17px; letter-spacing: .01em; }
  .ttt__title small { color: var(--muted); font-weight: 500; font-size: 12px; }
  .ttt__status { min-height: 22px; font-size: 14px; color: var(--muted); display: flex; align-items: center; gap: 8px; }
  .ttt__status.win { color: var(--win); font-weight: 600; }
  .ttt__status.lose { color: var(--lose); font-weight: 600; }
  .ttt__status.draw { color: var(--text); font-weight: 600; }
  .ttt__say { color: var(--accent); }
  .ttt__grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
  .ttt__cell {
    aspect-ratio: 1 / 1; border: 1px solid var(--line); border-radius: 16px;
    background: var(--panel); color: var(--text); font-size: 44px; font-weight: 700;
    display: flex; align-items: center; justify-content: center; cursor: pointer;
    transition: background .12s, transform .06s, box-shadow .12s; user-select: none;
  }
  .ttt__cell:hover:not(.filled):not(:disabled) { background: var(--accent-soft); }
  .ttt__cell:active:not(.filled) { transform: scale(.97); }
  .ttt__cell.filled { cursor: default; }
  .ttt__cell.x { color: var(--x); }
  .ttt__cell.o { color: var(--o); }
  .ttt__cell.win { box-shadow: inset 0 0 0 2px var(--accent); background: var(--accent-soft); }
  .ttt__grid.thinking .ttt__cell:not(.filled) { cursor: progress; }
  .ttt__foot { display: flex; align-items: center; justify-content: space-between; }
  .ttt__new {
    font: inherit; font-size: 13px; font-weight: 600; color: var(--text);
    background: var(--accent-soft); border: 1px solid var(--line); border-radius: 10px;
    padding: 9px 14px; cursor: pointer;
  }
  .ttt__new:hover { border-color: var(--accent); }
  .ttt__hint { font-size: 11px; color: var(--muted); }
  .dots { display: inline-flex; gap: 3px; }
  .dots i { width: 5px; height: 5px; border-radius: 50%; background: var(--accent); opacity: .35; animation: b 1s infinite; }
  .dots i:nth-child(2) { animation-delay: .15s; }
  .dots i:nth-child(3) { animation-delay: .3s; }
  @keyframes b { 0%,100%{opacity:.25;transform:translateY(0)} 50%{opacity:1;transform:translateY(-2px)} }
  :root{--win:#34d399;--lose:#ff6b82}
  :root[data-mode=light]{color-scheme:light;--text:#1d2a38;--muted:#55677e;--page:#f4f7fb;--panel:#ffffff;
    --line:rgba(100,116,139,.28);--accent:#7c5cd6;--accent-soft:rgba(124,92,214,.14);--x:#1e7fb8;--o:#7c5cd6;--win:#0e8a72;--lose:#c2314b}
</style><script src="__THEME_SYNC_JS__"></script>
</head>
<body>
  <div class="ttt">
    <div class="ttt__head">
      <div class="ttt__title">Tic-tac-toe <small>you vs <span data-aname>Solomiia</span></small></div>
    </div>
    <div class="ttt__status" id="status">Your move — you are <b style="color:var(--x);margin:0 3px">X</b></div>
    <div class="ttt__grid" id="grid"></div>
    <div class="ttt__foot">
      <button class="ttt__new" id="new">New game</button>
      <span class="ttt__hint"><span data-aname>Solomiia</span> (O) thinks with the model — a move can take a moment.</span>
    </div>
  </div>
<script src="__TICTACTOE_APP_JS__"></script>
</body></html>
HTML;

        // The page's scripts are static assets (fingerprinted, cached immutable);
        // nothing executable is left inline.
        $html = strtr($html, [
            '__THEME_SYNC_JS__' => AssetManager::getUrl('js/app-theme-sync.js', 'os'),
            '__TICTACTOE_APP_JS__' => AssetManager::getUrl('js/tictactoe-app.js', 'tictactoe'),
        ]);

        return $resource
            ->setContent(CspNonce::stamp($html))
            ->setHeader('Content-Type', 'text/html; charset=utf-8');
    }
}
