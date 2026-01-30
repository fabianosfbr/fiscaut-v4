<?php

namespace Tests\Feature;

use App\Models\TempFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TempFileDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_dono_consegue_baixar_arquivo_temporario(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $tempFile = TempFile::query()->create([
            'user_id' => $user->id,
            'disk' => 'local',
            'file_path' => 'temp-downloads/' . $user->id . '/arquivo.zip',
            'original_name' => 'arquivo.zip',
            'expires_at' => now()->addHour(),
        ]);

        Storage::disk('local')->put($tempFile->file_path, 'conteudo');

        $this
            ->actingAs($user)
            ->get(route('process-download.download', $tempFile))
            ->assertOk()
            ->assertDownload('arquivo.zip');
    }

    public function test_usuario_nao_dono_recebe_404(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $tempFile = TempFile::query()->create([
            'user_id' => $owner->id,
            'disk' => 'local',
            'file_path' => 'temp-downloads/' . $owner->id . '/arquivo.zip',
            'original_name' => 'arquivo.zip',
            'expires_at' => now()->addHour(),
        ]);

        Storage::disk('local')->put($tempFile->file_path, 'conteudo');

        $this
            ->actingAs($otherUser)
            ->get(route('process-download.download', $tempFile))
            ->assertNotFound();
    }

    public function test_arquivo_expirado_retorna_404(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $tempFile = TempFile::query()->create([
            'user_id' => $user->id,
            'disk' => 'local',
            'file_path' => 'temp-downloads/' . $user->id . '/arquivo.zip',
            'original_name' => 'arquivo.zip',
            'expires_at' => now()->subMinute(),
        ]);

        Storage::disk('local')->put($tempFile->file_path, 'conteudo');

        $this
            ->actingAs($user)
            ->get(route('process-download.download', $tempFile))
            ->assertNotFound();
    }

    public function test_path_invalido_retorna_404(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $tempFile = TempFile::query()->create([
            'user_id' => $user->id,
            'disk' => 'local',
            'file_path' => '../.env',
            'original_name' => 'arquivo.zip',
            'expires_at' => now()->addHour(),
        ]);

        $this
            ->actingAs($user)
            ->get(route('process-download.download', $tempFile))
            ->assertNotFound();
    }

    public function test_disk_invalido_retorna_404(): void
    {
        $user = User::factory()->create();

        $tempFile = TempFile::query()->create([
            'user_id' => $user->id,
            'disk' => 'nao_existe',
            'file_path' => 'temp-downloads/' . $user->id . '/arquivo.zip',
            'original_name' => 'arquivo.zip',
            'expires_at' => now()->addHour(),
        ]);

        $this
            ->actingAs($user)
            ->get(route('process-download.download', $tempFile))
            ->assertNotFound();
    }
}

