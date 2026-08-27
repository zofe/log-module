<?php

namespace App\Modules\Log\Ai;

use App\Modules\Log\Services\LogParser;
use Zofe\Rapyd\Contracts\AiTool;
use Zofe\Rapyd\Contracts\AiToolProvider;

class LogAiToolProvider implements AiToolProvider
{
    public function tools(): array
    {
        return [
            new AiTool(
                name: 'get_recent_errors',
                description: 'Returns recent entries from the Laravel application log. Use this to answer questions about errors, exceptions, warnings, or application health.',
                inputSchema: [
                    'type'       => 'object',
                    'properties' => [
                        'level' => [
                            'type'        => 'string',
                            'enum'        => ['error', 'critical', 'alert', 'emergency', 'warning', 'notice', 'info', 'debug'],
                            'description' => 'Filter by log level. Omit to return all levels.',
                        ],
                        'limit' => [
                            'type'        => 'integer',
                            'default'     => 20,
                            'description' => 'Maximum number of log entries to return.',
                        ],
                        'search' => [
                            'type'        => 'string',
                            'description' => 'Optional text to search within error messages and stack traces.',
                        ],
                    ],
                ],
                handler: function (array $input) {
                    return $this->getRecentErrors(
                        level: $input['level'] ?? null,
                        limit: $input['limit'] ?? 20,
                        search: $input['search'] ?? null,
                    );
                }
            ),

            new AiTool(
                name: 'get_error_summary',
                description: 'Returns a grouped summary of recent errors with frequency counts. Useful for understanding which errors are most common.',
                inputSchema: [
                    'type'       => 'object',
                    'properties' => [
                        'level' => [
                            'type'    => 'string',
                            'enum'    => ['error', 'critical', 'alert', 'emergency', 'warning'],
                            'default' => 'error',
                        ],
                        'top' => [
                            'type'        => 'integer',
                            'default'     => 10,
                            'description' => 'How many distinct errors to return, ordered by frequency.',
                        ],
                    ],
                ],
                handler: function (array $input) {
                    return $this->getErrorSummary(
                        level: $input['level'] ?? 'error',
                        top: $input['top'] ?? 10,
                    );
                }
            ),
        ];
    }

    // -------------------------------------------------------------------------

    protected function getRecentErrors(?string $level, int $limit, ?string $search): array
    {
        $parser  = new LogParser();
        $entries = $parser->parse($parser->latestFile() ?? '');

        if ($level) {
            $entries = array_filter($entries, fn ($e) => $e['level'] === $level);
        }

        if ($search) {
            $s       = strtolower($search);
            $entries = array_filter($entries, fn ($e) =>
                str_contains(strtolower($e['text'] ?? ''), $s) ||
                str_contains(strtolower($e['stack'] ?? ''), $s)
            );
        }

        // Strip stack trace for readability; keep only first line
        return array_slice(array_values(array_map(fn ($e) => [
            'level'   => $e['level'],
            'date'    => $e['date'],
            'context' => $e['context'],
            'message' => $e['text'],
            'stack'   => $e['stack'] ? substr($e['stack'], 0, 500) : null,
        ], $entries)), 0, $limit);
    }

    protected function getErrorSummary(string $level, int $top): array
    {
        $parser  = new LogParser();
        $entries = $parser->parse($parser->latestFile() ?? '');
        $entries = array_filter($entries, fn ($e) => $e['level'] === $level);

        $groups = [];
        foreach ($entries as $entry) {
            $key = md5($entry['text']);
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'message'   => $entry['text'],
                    'count'     => 0,
                    'last_seen' => $entry['date'],
                ];
            }
            $groups[$key]['count']++;
        }

        usort($groups, fn ($a, $b) => $b['count'] <=> $a['count']);

        return array_slice(array_values($groups), 0, $top);
    }
}
