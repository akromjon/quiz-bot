<?php

namespace App\Telegram\FSM;

use App\Models\Enums\TransactionStatusEnum;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use App\Models\TelegramUser;
use App\Telegram\Menu\Menu;
use Nette\Utils\Strings;
use Telegram\Bot\Objects\File;

class FileFSM extends Base
{
    protected string $file_path = '/receipt/';
    protected Collection $file;
    public static array $allowed_file_types = ['jpeg', 'jpg', 'heic', 'png', 'pdf'];

    public function route(): void
    {
        

        $file_id = is_int($this->file->last()) ? $this->file->file_id : $this->file->last()->file_id;

        $file = $this->getFileFromTelegram($file_id);

        if (400 === $file) {

            $this->sendMessage(Menu::fileSizeNotAllowedMessage());

            return;
        }

        $file_extension = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));

        if (!$this->isAllowedFileType($file_extension)) {

            $this->sendMessage(Menu::fileTypeNotAllowedMessage());

            return;
        }

        $file_path = $this->downloadAndStoreFile($file, $file_extension);

        $user= TelegramUser::getCurrentUser();

        $user->transactions()->create([
            'amount' => setting('tariff_amount') ?? 0,
            'receipt_path' => $file_path,
            'status' => TransactionStatusEnum::PENDING,
            'payment_date' => now(),
        ]);

        $this->sendMessage(Menu::receiptPending());

        TelegramUser::clearLastMessage();
    }

    private function getFileFromTelegram(string $fileId): File|int
    {
        try {

            return Telegram::getFile(['file_id' => $fileId]);

        } catch (\Exception $e) {

            Log::error($e->getMessage());

            return $e->getCode();
        }

    }

    private function isAllowedFileType(string $file_extension): bool
    {
        return in_array($file_extension, self::$allowed_file_types);
    }

    private function downloadAndStoreFile(File $file, string $file_extension): ?string
    {
        $user = TelegramUser::getCurrentUser();

        $short_path="$this->file_path{$user->user_id}_{$file->file_unique_id}.{$file_extension}";

        $full_path = storage_path("app/public/{$short_path}");

        Telegram::downloadFile($file->file_id, $full_path);

        return $short_path;
    }

}