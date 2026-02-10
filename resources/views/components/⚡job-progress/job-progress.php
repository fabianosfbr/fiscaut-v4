<?php

use Livewire\Component;
use App\Models\JobProgress;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

new class extends Component
{
    public ?string $jobId = null;
    public bool $poll = true;

    public int $progress = 0;
    public string $status = 'pending';
    public ?string $message = null;

    public bool $isVisible = false;

    public function mount(?string $jobId = null)
    {
        // Se o jobId não for passado via parâmetro, tenta pegar da sessão
        if (! $jobId) {
            $jobId = session()->get('jobProgressId');
        }

        if ($jobId) {
            $this->jobId = $jobId;
            $this->isVisible = true;
            $this->loadProgress();
        }
    }

    public function loadProgress()
    {
        if (! $this->jobId) {
            $this->isVisible = false;
            return;
        }

        $progress = JobProgress::find($this->jobId);

        if (! $progress) {
            $this->isVisible = false;
            return;
        }

        $this->progress = $progress->progress;
        $this->status   = $progress->status;
        $this->message  = $progress->message;

        // Se o status for 'done' ou 'failed', o job finalizou
        if (in_array($this->status, ['done', 'failed'])) {
            $this->poll = false;
            $this->isVisible = false;

            // Limpa a sessão para não reaparecer no próximo mount
            if (session()->get('jobProgressId') === $this->jobId) {
                session()->forget('jobProgressId');
            }

            // Dispara evento para atualizar tabelas ou outros componentes
            // No Livewire 3/Filament v3 usa-se dispatch()
            redirect(request()->header('Referer'));
        } else {
            $this->isVisible = true;
        }
    }
};
