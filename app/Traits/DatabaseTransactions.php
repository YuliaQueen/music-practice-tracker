<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Trait для безопасной работы с транзакциями базы данных
 */
trait DatabaseTransactions
{
    /**
     * Выполнить код в транзакции с обработкой ошибок
     *
     * @param callable $callback      Функция для выполнения в транзакции
     * @param string   $errorContext  Контекст ошибки для логирования
     * @param array    $logContext    Дополнительный контекст для логов
     * @return array{success: bool, result?: mixed, message: string}
     */
    protected function executeInTransaction(callable $callback, string $errorContext, array $logContext = []): array
    {
        try {
            DB::beginTransaction();

            $result = $callback();

            DB::commit();

            return [
                'success' => true,
                'result'  => $result,
                'message' => 'Операция выполнена успешно',
            ];
        } catch (\Throwable $e) {
            $this->handleTransactionError($e, $errorContext, $logContext);

            return [
                'success' => false,
                'message' => $errorContext,
            ];
        }
    }

    /**
     * Обработать ошибку транзакции
     *
     * @param \Throwable $e            Исключение
     * @param string     $errorContext Контекст ошибки
     * @param array      $logContext   Дополнительный контекст для логов
     * @return void
     */
    protected function handleTransactionError(\Throwable $e, string $errorContext, array $logContext = []): void
    {
        if (DB::transactionLevel() > 0) {
            try {
                DB::rollBack();
            } catch (\Throwable $rollbackException) {
                Log::error('Ошибка при откате транзакции', array_merge($logContext, [
                    'error' => $rollbackException->getMessage(),
                ]));
            }
        }

        Log::error($errorContext, array_merge($logContext, [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]));
    }
}