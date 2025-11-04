<?php

declare(strict_types=1);

namespace Notifier\Auth\Infrastructure\Controllers;

use App\Http\Requests\Auth\RegisterRequest;
use Notifier\Auth\Application\Ports\In\RegisterUserPort;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Notifier\Auth\Domain\User\Exceptions\EmailAlreadyExistsException;
use Notifier\Auth\Domain\User\Exceptions\EmptyUserIdException;

final class RegisterController
{
    public function __construct(
        private RegisterUserPort $registerUsePort
    ) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            $user = $this->registerUsePort->execute($validatedData);

            return new JsonResponse([
                'message' => trans('messages.user.registered_success'),
                'user' => [
                    'id' => $user->id()->value(),
                    'name' => $user->name(),
                    'email' => $user->email()->value(),
                ]
            ], 201);
        } catch (InvalidArgumentException $e) {
            $message = $e->getMessage();
            
            $translationKey = match($message) {
                'Email must contain a dot.' => 'validation.email.missing_dot',
                default => null
            };
            
            return new JsonResponse([
                'message' => trans('messages.validation.error'),
                'errors' => ['validation' => [
                    $translationKey ? trans($translationKey, ['attribute' => 'email']) : $message
                ]]
            ], 422);
        } catch (EmailAlreadyExistsException $e) {
            return new JsonResponse([
                'error' => trans('messages.user.email_already_exists')
            ], 409);
        } catch (EmptyUserIdException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
                'error' => 'INTERNAL_SERVER_ERROR'
            ], 500);
        }
    }
}