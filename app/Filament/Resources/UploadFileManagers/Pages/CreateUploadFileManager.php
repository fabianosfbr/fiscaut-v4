<?php

namespace App\Filament\Resources\UploadFileManagers\Pages;

use App\Filament\Resources\UploadFileManagers\UploadFileManagerResource;
use App\Models\Tag;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CreateUploadFileManager extends CreateRecord
{
    protected static string $resource = UploadFileManagerResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        $data['doc_value'] = str_replace(',', '.', str_replace('.', '', $data['doc_value']));
        $data['user_id'] = $user->id;
        $data['tenant_id'] = $user->tenant_id;
        $data['issuer_id'] = currentIssuer($user)->id;

        if (isset($data['arquivo'])) {
            $data['path'] = $data['arquivo'];
            $data['extension'] = pathinfo($data['arquivo'], PATHINFO_EXTENSION);
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);
        $record->save();
        $this->taggedFile($data, $record);

        return $record;
    }

    protected function taggedFile(array $data, Model $record)
    {
        foreach ($data['tags'] as $tag_apply) {
            $tag = Tag::find($tag_apply['tag_id']);

            $record->tag($tag->id, $tag_apply['valor']);

        }
        Cache::forget('tags_used_in_upload_file_'.currentIssuer()->id);
    }

    protected function getCreateFormAction(): Action
    {
        $hasFormWrapper = $this->hasFormWrapper();

        return Action::make('create')
            ->label('Enviar')
            ->submit($hasFormWrapper ? $this->getSubmitFormLivewireMethodName() : null)
            ->action($hasFormWrapper ? null : $this->getSubmitFormLivewireMethodName())
            ->keyBindings(['mod+s']);
    }

    protected function getRedirectUrl(): string
    {

        return $this->getResource()::getUrl('index');
    }
}
