<?php

declare(strict_types=1);

namespace App\Controller;

use App\GraphQL\Queries\QueryType;
use App\GraphQL\Mutations\MutationType;
use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use GraphQL\Error\DebugFlag;
use RuntimeException;
use Throwable;

/**
 * GraphQL Controller
 * 
 * Handles all GraphQL requests
 */
class GraphQL
{
    /**
     * Handle GraphQL request
     * 
     * @return string JSON response
     */
    public static function handle(): string
    {
        try {
            // Build schema with our custom types
            $schema = new Schema(
                (new SchemaConfig())
                    ->setQuery(new QueryType())
                    ->setMutation(new MutationType())
            );

            // Get input
            $rawInput = file_get_contents('php://input');
            if ($rawInput === false) {
                throw new RuntimeException('Failed to read request body');
            }

            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException('Invalid JSON in request body');
            }

            $query = $input['query'] ?? '';
            $variableValues = $input['variables'] ?? null;
            $operationName = $input['operationName'] ?? null;

            // Execute query
            $result = GraphQLBase::executeQuery(
                $schema,
                $query,
                null,
                null,
                $variableValues,
                $operationName
            );

            // Format output
            $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true'
                ? DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE
                : DebugFlag::NONE;

            $output = $result->toArray($debug);
        } catch (Throwable $e) {
            $output = [
                'errors' => [
                    [
                        'message' => $e->getMessage(),
                        'extensions' => [
                            'category' => 'internal'
                        ]
                    ]
                ]
            ];

            // Add trace in debug mode
            if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
                $output['errors'][0]['extensions']['trace'] = $e->getTraceAsString();
            }
        }

        header('Content-Type: application/json; charset=UTF-8');
        return json_encode($output);
    }
}
