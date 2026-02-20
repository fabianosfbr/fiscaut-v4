<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SecureDownload>
 */
class SecureDownloadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'file_path' => 'downloads/'.$this->faker->uuid.'.zip',
            'file_name' => $this->faker->word.'.zip',
            'mime_type' => 'application/zip',
            'size' => $this->faker->numberBetween(100, 1000000),
            'job_class' => 'App\Jobs\BulkAction\DownloadUploadFileBulkActionJob',
            'expires_at' => now()->addDays(7),
        ];
    }
}
