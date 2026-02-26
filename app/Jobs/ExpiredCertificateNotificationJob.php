<?php

namespace App\Jobs;

use App\Models\Issuer;
use App\Models\Tenant;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExpiredCertificateNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $issuers = Issuer::where('is_enabled', true)
                ->where('validade_certificado', '<=', now())
                ->where('tenant_id', $tenant->id)
                ->select(['razao_social'])
                ->get();
            if ($issuers->isNotEmpty()) {
                $users = User::where('tenant_id', $tenant->id)
                    ->whereHas('roles', function ($q) {
                        $q->where('slug', 'admin');
                    })
                    ->distinct()->get();

                foreach ($users as $user) {

                    // Mail::to($user->email)->queue(new ExpiredCertificationNotificationEmail(['url' => url('/app/issuers')]));

                    Notification::make()
                        ->title('Vencimento de certificado digital')
                        ->body('Existe empresa com certificado vencendo e/ou vencido. Por favor, atualize o mais rápido possível.')
                        ->actions([
                            Action::make('view')
                                ->label('Ver detalhes')
                                ->button()
                                ->openUrlInNewTab()
                                ->url(
                                    route('filament.app.resources.issuers.index', [
                                        'filters' => [
                                            'is_enabled' => ['value' => 'true'],
                                            'certificado_vencendo' => ['isActive' => 'false'],
                                            'certificado_vencido' => ['isActive' => 'true'],
                                            'sem_certificado' => ['isActive' => 'false'],
                                        ],
                                    ])
                                ),
                        ])
                        ->icon('heroicon-o-exclamation-circle')
                        ->sendToDatabase($user, isEventDispatched: true);
                }
            }
        }
    }
}
