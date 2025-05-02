<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ///Actions\DeleteAction::make(),
        ];
    }
    protected function getFormActions(): array
    {
        // Return an empty array to remove all form action buttons (like "Save changes", "Cancel", etc.)
        return [];
    }
}
