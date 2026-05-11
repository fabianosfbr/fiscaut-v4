<?php

namespace App\Jobs;

use App\Mail\CobrancaEmail;
use App\Models\GeneralSetting;
use App\Models\Issuer;
use App\Models\SuperLogicaCobrancaNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SendCobrancaEmailJob implements ShouldQueue
{
    use Queueable;

    public int $issuerId;

    public string $recipientEmail;

    public array $unidadeData;

    public bool $isJuridico;

    /**
     * Create a new job instance.
     *
     * @param  int  $issuerId  ID of the issuer (company)
     * @param  string  $recipientEmail  Email address to send the cobranca
     * @param  array  $unidadeData  Array containing keys like 'numero_unidade', 'bloco_quadra', 'nome_morador', 'titulos_aberto'
     * @param  bool  $isJuridico  Whether to use the legal/juridico template
     */
    public function __construct(int $issuerId, string $recipientEmail, array $unidadeData, bool $isJuridico = false)
    {
        $this->issuerId = $issuerId;
        $this->recipientEmail = $recipientEmail;
        $this->unidadeData = $unidadeData;
        $this->isJuridico = $isJuridico;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $issuer = Issuer::with('tenant')->find($this->issuerId);

        if (! $issuer || ! $issuer->tenant) {
            return; // Cannot send without tenant/issuer info
        }

        $tenant = $issuer->tenant;

        $notification = SuperLogicaCobrancaNotification::create([
            'tenant_id' => $tenant->id,
            'issuer_id' => $this->issuerId,
            'id_recebimento_recb' => $this->unidadeData['id_recebimento_recb'],
            'id_unidade_uni' => $this->unidadeData['id_unidade_uni'],
            'data' => [
                'recipient_email' => $this->recipientEmail,
                'unidade_data' => $this->unidadeData,
                'subject' => $this->isJuridico
                    ? 'Cobrança Encaminhada ao Jurídico - Unidade '.($this->unidadeData['numero_unidade'] ?? '')
                    : 'Aviso de Débito - Unidade '.($this->unidadeData['numero_unidade'] ?? ''),
                'recebimento' => $this->unidadeData['recebimento'] ?? null,
                'is_juridico' => $this->isJuridico,
            ],
        ]);

        try {
            $this->sendEmail($tenant, $issuer);

            $notification->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            $notification->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function sendEmail($tenant, $issuer): void
    {

        // Configure dynamic mailer for this tenant
        $mailerName = 'tenant_'.$tenant->id;
        Config::set("mail.mailers.{$mailerName}", [
            'transport' => 'smtp',
            'host' => $tenant->smtp_host,
            'port' => $tenant->smtp_port,
            'encryption' => $tenant->smtp_encryption,
            'username' => $tenant->smtp_username,
            'password' => $tenant->smtp_password,
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ]);

        // Fetch the template from GeneralSetting
        $settingName = $this->isJuridico ? 'template_cobranca_juridico' : 'template_cobranca';
        $settingKey = $this->isJuridico ? 'mensagem_juridico' : 'mensagem';

        $settings = GeneralSetting::where('issuer_id', $this->issuerId)
            ->where('name', $settingName)
            ->first();

        $template = $settings ? ($settings->payload[$settingKey] ?? null) : null;

        if (! $template) {
            // Default templates if not found
            if ($this->isJuridico) {
                $template = <<<'HTML'
                            <p><strong>Unidade {{numero_unidade}} Bloco {{bloco_quadra}}</strong></p>
                            <p>Prezado(a) {{nome_morador}},</p>
                            <p>Após múltiplas tentativas de contato e notificação, lamentamos informar que até o momento não recebemos resposta ou pagamento.</p>
                            <p>Detalhes da(s) pendência(s):</p>
                            <p>{{titulos_aberto}}</p>
                            <p>Por esta razão, o caso será encaminhharado para o departamento jurídico do condomínio.</p>
                            <p>Caso deseja evitar medidas judiciais, entre em contato conosco urgentemente.</p>
                            <p>Atenciosamente,</p>
                            HTML;
            } else {
                $template = <<<'HTML'
                            <p><strong>Unidade {{numero_unidade}} Bloco {{bloco_quadra}}</strong></p>
                            <p>Prezado(a) {{nome_morador}},</p>
                            <p>Esperamos que esteja bem.</p>
                            <p>Identificamos em nosso sistema a existência de pendência(s) referente(s) às taxas condominiais da unidade {{numero_unidade}}.</p>
                            <p>Detalhes da(s) pendência(s):</p>
                            <p>{{titulos_aberto}}</p>
                            <p>Pedimos a gentileza de verificar e, se possível, regularizar o(s) débito(s) o quanto antes.</p>
                            <p>Atenciosamente,</p>
                            HTML;
            }
        }

        // Replace variables
        $body = $template;
        foreach (['numero_unidade', 'bloco_quadra', 'nome_morador', 'titulos_aberto'] as $var) {
            $value = $this->unidadeData[$var] ?? '';
            // Handle both {{var}} and {{ var }}
            $body = str_replace('{{'.$var.'}}', $value, $body);
            $body = str_replace('{{ '.$var.' }}', $value, $body);
        }

        $subject = $this->isJuridico
            ? 'Cobrança Encaminhada ao Jurídico - Condomínio'.($this->unidadeData['razao_social'] ?? '')
            : 'Aviso de Débito - Condomínio'.($this->unidadeData['razao_social'] ?? '');

        $fromEmail = (string) ($tenant->smtp_from_email ?: config('mail.from.address', 'noreply@fiscaut.com.br'));
        $fromName = (string) ($tenant->smtp_from_name ?: config('mail.from.name', 'Fiscaut'));
        $tenantName = $tenant->name ?? 'Fiscaut';

        // Logo URL do tenant (se existir) ou usar padrão
        $logoUrl = $tenant->logo ? asset('storage/'.$tenant->logo) : null;

        // Tratar múltiplos destinatários separados por ponto e vírgula
        $recipients = collect(explode(';', $this->recipientEmail))
            ->map(fn ($email) => trim($email))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->toArray();

        if (empty($recipients)) {
            return; // Nenhum e-mail válido encontrado
        }

        Mail::mailer($mailerName)->to($recipients)->send(new CobrancaEmail($subject, $body, $fromEmail, $fromName, $tenantName, $logoUrl));
    }
}
