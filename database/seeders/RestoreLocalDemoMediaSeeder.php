<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\Manager;
use App\Models\MusicalGender;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;
use Spatie\MediaLibrary\HasMedia;

class RestoreLocalDemoMediaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 21) as $id) {
            $this->attachLocalMedia(User::findOrFail($id), 'profile_images', $id);
        }

        foreach (range(1, 7) as $id) {
            $this->attachLocalMedia(MusicalGender::findOrFail($id), 'musical_gender_image', $id + 21);
        }

        foreach (range(1, 15) as $id) {
            $mediaId = 29 + (($id - 1) * 2);

            $this->attachLocalMedia(Artist::findOrFail($id), 'artist_image', $mediaId);
            $this->attachLocalMedia(Manager::findOrFail($id), 'manager_image', $mediaId + 1);
        }
    }

    private function attachLocalMedia(HasMedia $model, string $collection, int $legacyMediaId): void
    {
        $existingMedia = $model->getFirstMedia($collection);

        if ($existingMedia && is_file($existingMedia->getPath()) && filesize($existingMedia->getPath()) > 0) {
            return;
        }

        if ($existingMedia) {
            $model->clearMediaCollection($collection);
        }

        $sourceMediaId = $legacyMediaId;
        $directory = database_path('seeders/media');
        $files = $this->getSeedMediaFiles($sourceMediaId);

        if (count($files) !== 1 || filesize($files[0]) === 0) {
            if ($legacyMediaId === 1) {
                $sourceMediaId = 2;
                $files = $this->getSeedMediaFiles($sourceMediaId);
            }
        }

        if (count($files) !== 1 || filesize($files[0]) === 0) {
            throw new RuntimeException("Expected one seeded media file for legacy media [{$legacyMediaId}] in [{$directory}].");
        }

        $stagingDirectory = storage_path('app/media-restore-staging');

        if (!is_dir($stagingDirectory) && !mkdir($stagingDirectory, 0775, true) && !is_dir($stagingDirectory)) {
            throw new RuntimeException("Unable to create media staging directory [{$stagingDirectory}].");
        }

        $stagedFile = $stagingDirectory . DIRECTORY_SEPARATOR . $legacyMediaId . '-' . basename($files[0]);

        if (!copy($files[0], $stagedFile)) {
            throw new RuntimeException("Unable to stage local media file [{$files[0]}].");
        }

        $model->addMedia($stagedFile)
            ->preservingOriginal()
            ->toMediaCollection($collection, 'public');

        if (!unlink($stagedFile)) {
            throw new RuntimeException("Unable to remove staged media file [{$stagedFile}].");
        }
    }

    /**
     * Seed media is kept under database/seeders/media so deployments do not
     * depend on the ignored runtime storage directory.
     */
    private function getSeedMediaFiles(int $legacyMediaId): array
    {
        return array_values(array_filter(
            glob(database_path("seeders/media/{$legacyMediaId}-*")) ?: [],
            static fn (string $file): bool => is_file($file) && filesize($file) > 0
        ));
    }
}
