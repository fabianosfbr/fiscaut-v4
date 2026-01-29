<div class="space-y-4">
    @if ($suggestions->isEmpty())
        <p class="text-sm text-gray-500">
            Nenhuma sugestão encontrada para este emitente.
        </p>
    @else
        <div style="overflow:hidden; border-radius: 12px; border: 1px solid #e5e7eb; background: #ffffff;">
            <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                <colgroup>
                    <col style="width: 70%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                </colgroup>
                <thead>
                    <tr style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                        <th scope="col" style="padding: 10px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280;">
                            Etiqueta
                        </th>
                        <th scope="col" style="padding: 10px 16px; text-align: right; font-size: 12px; font-weight: 600; color: #6b7280;">
                            Qtde
                        </th>
                        <th scope="col" style="padding: 10px 16px; text-align: right; font-size: 12px; font-weight: 600; color: #6b7280;">
                            Ação
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($suggestions as $suggestion)
                        <tr style="border-top: 1px solid #f3f4f6;">
                            <td style="padding: 10px 16px; font-size: 14px; color: #111827;">
                                <span style="display:block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ filled($suggestion->code) ? $suggestion->code.' - ' : '' }}{{ $suggestion->tag_name }}
                                </span>
                            </td>
                            <td style="padding: 10px 16px; font-size: 14px; color: #111827; text-align: right; white-space: nowrap;">
                                {{ $suggestion->qtde }}
                            </td>
                            <td style="padding: 10px 16px; text-align: right; white-space: nowrap;">
                                <x-filament::icon-button
                                    type="button"
                                    icon="heroicon-m-check"
                                    color="primary"
                                    wire:click.prevent="applySuggestedTag({{ $record->id }}, {{ $suggestion->tag_id }})"
                                    label="Aplicar"
                                />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
