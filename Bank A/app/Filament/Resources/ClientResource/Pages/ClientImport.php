<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ClientImport extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string $resource = ClientResource::class;
    protected static string $view = 'filament.resources.client-resource.pages.client-import';
    protected static ?string $title = 'Search Clients in Central KYC Database';

    public $national_id;
    public $citizenship_number;
    public $name;
    public $dob;
    public $filtered = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(3)->schema([
                TextInput::make('national_id')
                    ->label('National ID')
                    ->reactive(),
                TextInput::make('citizenship_number')
                    ->label('Citizenship Number')
                    ->reactive(),
                TextInput::make('name')
                    ->reactive(),
                DatePicker::make('dob')
                    ->label('Date of Birth')
                    ->reactive(),
            ]),
        ];
    }

    public function filter(): void
    {
        // Sync form values with component properties
        // $this->form->fill();

        // Reset pagination and refresh table
        $this->filtered = true;
        if ($this->national_id || $this->citizenship_number || $this->name || $this->dob) {
            $this->resetPage();
            $this->getTableRecords();
        } else {
            $this->filtered = false;
            Notification::make()
                ->title('Please provide at least one search criteria.')
                ->danger()
                ->send();
        }
    }

    protected function getTableQuery(): Builder
    {
        return (new Client)
            ->setConnection('central')
            ->newQuery()
            ->when($this->national_id, fn($query) => $query->where('national_id', 'like', "%{$this->national_id}%"))
            ->when($this->citizenship_number, fn($query) => $query->where('citizenship_number', 'like', "%{$this->citizenship_number}%"))
            ->when($this->name, fn($query) => $query->where('name', 'like', "%{$this->name}%"))
            ->when($this->dob, fn($query) => $query->whereDate('dob', $this->dob));
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name'),
            TextColumn::make('father_name')
                ->label('Father Name'),
            TextColumn::make('email'),
            TextColumn::make('national_id')
                ->label('National ID'),
            TextColumn::make('citizenship_number')
                ->label('Citizenship Number'),
            TextColumn::make('dob')
                ->label('Date of Birth')
                ->date(),
        ];
    }
}
