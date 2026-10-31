<?php

namespace App\Actions;

use App\Events\GiftFetchCompleted;
use App\Models\Gift;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl;
use Throwable;

class ProcessGiftImageAction
{
    public function fromUrl(Gift $gift, string $imageUrl): bool
    {
        return Cache::lock('gift-image-'.$gift->id, 60)->block(30, function () use ($gift, $imageUrl): bool {
            if (in_array('image', $gift->fresh()->edited_fields ?? [], true)) {
                return false;
            }

            return $this->download($gift, $imageUrl);
        });
    }

    private function download(Gift $gift, string $imageUrl): bool
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
        return Cache::lock('gift-image-'.$gift->id, 60)->block(30, fn (): bool => $this->upload($gift, $file));
    }

    private function upload(Gift $gift, UploadedFile $file): bool
    {
        try {
            $gift->addMedia($file)
                ->toMediaCollection('image');
            $gift->getConnection()->transaction(function () use ($gift): void {
                $gift->refreshForUpdate();
                $gift->update(['edited_fields' => array_values(array_unique([...($gift->edited_fields ?? []), 'image']))]);
            });

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
        try {
            GiftFetchCompleted::dispatch($gift->fresh()->load('lists', 'media'));
        } catch (BroadcastException) {
            // Silently ignore - WebSocket server may be unavailable
        }
    }

    private function normalizeUrl(string $url): string
    {
        if (str_starts_with($url, '//')) {
            return 'https:'.$url;
        }

        return $url;
    }
}
