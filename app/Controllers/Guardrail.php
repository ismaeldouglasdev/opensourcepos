<?php

namespace App\Controllers;

/**
 * Endpoints publicos de coleta para o sistema guardrails (auto-heal).
 * Nao exige login: usado para capturar erros de JavaScript do browser
 * que nunca chegariam aos logs do servidor (ex.: botao que "nao funciona").
 */
class Guardrail extends BaseController
{
    public function jsError(): void
    {
        $request = $this->request;
        $is_json = str_contains((string)$request->getHeaderLine('Content-Type'), 'application/json');
        $json = $is_json ? $request->getJSON(true) : null;

        $msg = trim((string)$request->getPost('message'));
        if ($msg === '' && is_array($json)) {
            $msg = trim((string)($json['message'] ?? ''));
        }
        $stack = trim((string)$request->getPost('stack'));
        if ($stack === '' && is_array($json)) {
            $stack = trim((string)($json['stack'] ?? ''));
        }

        if ($msg === '' && $stack === '') {
            $this->response->setStatusCode(204);

            return;
        }

        $get = static fn(string $key): string => trim((string)(is_array($json) ? ($json[$key] ?? $request->getPost($key)) : $request->getPost($key)));

        $entry = [
            't'     => date('Y-m-d H:i:s'),
            'ip'    => $request->getIPAddress(),
            'url'   => mb_substr($get('url'), 0, 300),
            'msg'   => mb_substr($msg, 0, 500),
            'src'   => mb_substr($get('source'), 0, 300),
            'line'  => (int)$get('line'),
            'col'   => (int)$get('col'),
            'stack' => mb_substr($stack, 0, 1500),
            'ua'    => mb_substr((string)$request->getUserAgent(), 0, 200)
        ];

        $log_dir = WRITEPATH . 'logs';

        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0775, true);
        }

        @file_put_contents($log_dir . '/js-errors.log', json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

        $this->response->setStatusCode(204);
    }
}
