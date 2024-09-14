<?php

use App\Models\TelegramUser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Exceptions\TelegramOtherException;
use Telegram\Bot\Exceptions\CouldNotUploadInputFile;
use Telegram\Bot\Exceptions\ResponseParameters;
use Telegram\Bot\Exceptions\TelegramBotNotFoundException;
use Telegram\Bot\Exceptions\TelegramEmojiMapFileNotFoundException;
use Telegram\Bot\Exceptions\TelegramResponseException;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Exceptions\TelegramUndefinedPropertyException;

use Telegram\Bot\Laravel\Facades\Telegram;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        api: __DIR__ . '/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'bot/JWOaF3FJrqvt4kDYPVlx'
        ]);

        $middleware->trustProxies(at: '*');
    })

    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (TelegramResponseException $e) {

            if (403 === $e->getCode()) {

                $update = getWebhookUpdate();

                $user_id = $update->getChat()->getId();

                if (is_int($user_id)) {

                    TelegramUser::where('user_id', $user_id)->update(['status' => 'blocked']);
                }

                Log::error("User $user_id is blocked the bot");

                return response()->json([
                    'message' => 'unauthorized',
                ], 200);
            }

            // ...
        });

        // TelegramOtherException

        $exceptions->render(function (TelegramOtherException $e) {

            Log::error($e->getMessage());

            return response()->json([
                'message' => 'unauthorized',
            ], 200);


        });


        $exceptions->render(function (TelegramResponseException $e, Request $request) {

            Log::error($e->getMessage());

            return response()->json(['message' => 'Invalid order'], 200);
        });

        $exceptions->render(function (CouldNotUploadInputFile $e, Request $request) {

            Log::error($e->getMessage());

            return response()->json(['message' => 'Invalid order'], 200);
        });

        $exceptions->render(function (TelegramBotNotFoundException $e, Request $request) {

            Log::error($e->getMessage());

            return response()->json(['message' => 'Invalid order'], 200);
        });

        $exceptions->render(function (TelegramEmojiMapFileNotFoundException $e, Request $request) {

            Log::error($e->getMessage());

            return response()->json(['message' => 'Invalid order'], 200);
        });

        $exceptions->render(function (TelegramResponseException $e, Request $request) {

            Log::error($e->getMessage());

            return response()->json(['message' => 'Invalid order'], 200);
        });

        $exceptions->render(function (TelegramSDKException $e, Request $request) {

            Log::error($e->getMessage());

            return response()->json(['message' => 'Invalid order'], 200);
        });

        $exceptions->render(function (TelegramUndefinedPropertyException $e, Request $request) {

            Log::error($e->getMessage());

            return response()->json(['message' => 'Invalid order'], 200);
        });




        // Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException

        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, Request $request) {

            Log::error($e->getMessage());

            return response()->json(['message' => 'Route Not Found'], 404);
        });




    })->create();
