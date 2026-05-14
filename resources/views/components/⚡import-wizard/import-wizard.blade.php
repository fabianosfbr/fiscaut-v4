<div>
    <div class="fiwa-container" wire:poll.{{ $step === 3 && in_array($importStatus, ['pending', 'processing']) ? '2000ms' : 'stop' }}>
        <div class="fiwa-header">
            <h2>Importar Arquivo</h2>
        </div>

        <div class="fiwa-steps">
            <div class="fiwa-step {{ $step >= 1 ? 'active' : '' }}" wire:click="setStep(1)">
                <span class="fiwa-step-number">1</span>
                <span class="fiwa-step-label">Enviar Arquivo</span>
            </div>
            <div class="fiwa-step {{ $step >= 2 ? 'active' : '' }}" wire:click="setStep(2)">
                <span class="fiwa-step-number">2</span>
                <span class="fiwa-step-label">Revisar Dados</span>
            </div>
            <div class="fiwa-step {{ $step >= 3 ? 'active' : '' }}" wire:click="setStep(3)">
                <span class="fiwa-step-number">3</span>
                <span class="fiwa-step-label">Importar Dados</span>
            </div>

        </div>

        <?php $isDisabled = $status === 'processing'; ?>
        <div class="fiwa-content" @if($isDisabled) style="pointer-events: none; opacity: 0.6;" @endif>
            @switch($step)
            @case(1)
            <div>
                <div class="fiwa-upload-zone" onclick="document.getElementById('file-input').click()">
                    <input type="file"
                        id="file-input"
                        wire:model="uploadedFile"
                        accept=".xlsx,.xls"
                        class="fiwa-file-input">

                    <div class="fiwa-upload-placeholder">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="fiwa-upload-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                        </svg>
                        <p>Clique para selecionar um arquivo</p>
                        <span>Formatos suportados: .xlsx, .xls</span>
                    </div>

                </div>

                @if($this->uploadedFile)
                <div class="fiwa-file-preview">
                    <span class="fiwa-file-preview-name">{{ $this->uploadedFile->getClientOriginalName() }}</span>

                </div>
                @endif


            </div>

            @break
            @case(2)
            <div>
                @if(!empty($fileValidation['errors']))
                <div class="fiwa-alert fiwa-alert-error">
                    <p class="fiwa-alert-title">Erro na Validação</p>
                    <p class="fiwa-alert-message">{{ $fileValidation['errors'][0] }}</p>
                </div>
                @else
                <div class="fiwa-alert fiwa-alert-success">
                    <p class="fiwa-alert-title">Arquivo válido!</p>
                    <p class="fiwa-alert-message">Total de linhas a importar: <strong>{{ $fileValidation['total_rows'] }}</strong></p>
                </div>

                <div class="fiwa-preview">
                    <h4 class="fiwa-preview-title">Prévia (primeiras 5 linhas)</h4>
                    <div class="fiwa-preview-wrapper">
                        <table class="fiwa-preview-table">
                            <thead>
                                <tr>
                                    @foreach($fileValidation['headers'] as $header)
                                    <th>{{ $header }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fileValidation['preview'] as $row)
                                <tr>
                                    @foreach($fileValidation['headers'] as $header)
                                    <td>{{ $row[$header] ?? '' }}</td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
            @break
            @case(3)
            <div wire:poll.3s="refreshProgress" class="fiwa-progress">

                <x-filament::callout
                    icon="heroicon-o-information-circle"
                    color="info">
                    <x-slot name="heading">
                        Dados Importados: {{ $importProgress }}%
                       
                    </x-slot>

                    <x-slot name="description">
                        {{ $importMessage }}
                    </x-slot>
                </x-filament::callout>
            </div>
            @break
            @endswitch
        </div>

        <div class="fiwa-actions" @if($isDisabled) style="pointer-events: none; opacity: 0.6;" @endif>
            @if($step > 1 && $step < 3)
                <button type="button" wire:click="previousStep" class="fiwa-btn-secondary">
                Voltar
                </button>
                @endif

                @if($step < 3)
                    <button type="button" wire:click="nextStep" class="fiwa-btn-primary">
                    Avançar
                    </button>
                    @endif
        </div>
    </div>
</div>