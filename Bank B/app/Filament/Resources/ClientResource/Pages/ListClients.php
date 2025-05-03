<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->icon('heroicon-o-plus'),
            Action::make('importClients')
                ->label('Import Clients from Central KYC System')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->url(route('filament.admin.resources.clients.import')),
        ];
    }
}
