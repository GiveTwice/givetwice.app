<?php

namespace App\Actions;

use App\Events\GiftFetchCompleted;
use App\Models\Gift;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl;
use Throwable;

class ProcessGiftImageAction
{
    public function fromUrl(Gift $gift, string $imageUrl): bool
    {
        $imageUrl = $this->normalizeUrl($imageUrl);

        try {
            $gift->addMediaFromUrl($imageUrl)
                ->toMediaCollection('image');

            return true;
        } catch (UnreachableUrl $e) {
            Log::warning('Failed to download gift image: URL unreachable', [
                'gift_id' => $gift->id,
                'image_url' => $imageUrl,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (Throwable $e) {
            Log::warning('Exception downloading gift image', [
                'gift_id' => $gift->id,
                'image_url' => $imageUrl,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function fromUpload(Gift $gift, UploadedFile $file): bool
    {
        try {
            $gift->addMedia($file)
                ->toMediaCollection('image');

            return true;
        } catch (Throwable $e) {
            Log::warning('Exception uploading gift image', [
                'gift_id' => $gift->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function dispatchCompletedEvent(Gift $gift): void
    {
        GiftFetchCompleted::dispatch($gift->fresh()->load('lists', 'media'));
    }

    private function normalizeUrl(string $url): string
    {
        if (str_starts_with($url, '//')) {
            return 'https:'.$url;
        }

        return $url;
    }
}
