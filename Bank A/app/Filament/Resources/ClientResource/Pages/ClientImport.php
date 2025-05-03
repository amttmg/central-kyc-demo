<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\CentralClient;
use App\Models\Client;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Actions\Action;
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
        return CentralClient::query()
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

    protected function getTableActions(): array
    {
        return [
            Action::make('view')
                ->label('View Details')
                ->icon('heroicon-o-eye')
                ->modalHeading(fn(CentralClient $record) => "Client Details: {$record->name}")
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->form(fn(CentralClient $record) => [
                    Section::make('Client Image')
                        ->schema([
                            FileUpload::make('img')
                                ->label('Client Image')
                                ->image()
                                ->disk('public')
                                ->directory('client_images')
                                ->disabled()
                                ->dehydrated(false)
                        ])
                        ->columns(1),
                    Section::make('Basic Information')
                        ->schema([
                            TextInput::make('name')
                                ->label('Full Name')->disabled(),
                            TextInput::make('national_id')
                                ->label('National ID')->disabled(),
                            TextInput::make('dob')
                                ->label('Date of Birth')->disabled(),
                            //->formatStateUsing(fn($state) => $state?->format('Y-m-d')),

                        ])
                        ->columns(2),

                    Section::make('Contact Information')
                        ->schema([
                            TextInput::make('email')->disabled(),
                            TextInput::make('phone')->disabled(),
                            TextInput::make('permanent_address')
                                ->label('Permanent Address')
                                ->columnSpanFull()->disabled(),
                            TextInput::make('temporary_address')
                                ->label('Temporary Address')
                                ->columnSpanFull()->disabled(),
                        ])
                        ->columns(2),

                    Section::make('Family Information')
                        ->schema([
                            TextInput::make('father_name')
                                ->label('Father Name')->disabled(),
                            TextInput::make('mother_name')
                                ->label('Mother Name')->disabled(),
                            TextInput::make('spouse_name')
                                ->label('Spouse Name')->disabled(),
                            TextInput::make('marital_status')
                                ->label('Marital Status')->disabled(),
                        ])
                        ->columns(2),

                    Section::make('Citizenship Details')
                        ->schema([
                            TextInput::make('citizenship_number')
                                ->label('Citizenship Number')->disabled(),
                            TextInput::make('citizenship_issued_date')
                                ->label('Issued Date')->disabled(),
                            //->formatStateUsing(fn($state) => $state?->format('Y-m-d')),
                            TextInput::make('citizenship_issued_place')
                                ->label('Issued Place')
                                ->columnSpanFull()->disabled(),
                        ])
                        ->columns(2),

                    Section::make('Financial Information')
                        ->schema([
                            TextInput::make('occupation')->disabled(),
                            TextInput::make('income_source')
                                ->label('Income Source')->disabled(),
                            TextInput::make('income_range')
                                ->label('Income Range')->disabled(),
                        ])
                        ->columns(2),
                ])
                ->fillForm(fn(CentralClient $record) => [
                    'name' => $record->name,
                    'email' => $record->email,
                    'phone' => $record->phone,
                    'citizenship_number' => $record->citizenship_number,
                    'citizenship_issued_date' => $record->citizenship_issued_date,
                    'citizenship_issued_place' => $record->citizenship_issued_place,
                    'father_name' => $record->father_name,
                    'mother_name' => $record->mother_name,
                    'spouse_name' => $record->spouse_name,
                    'permanent_address' => $record->permanent_address,
                    'temporary_address' => $record->temporary_address,
                    'occupation' => $record->occupation,
                    'income_source' => $record->income_source,
                    'income_range' => $record->income_range,
                    'marital_status' => $record->marital_status,
                    'national_id' => $record->national_id,
                    'dob' => $record->dob,
                    'img' => $record->img,
                ]),
            Action::make('Pull')
                ->requiresConfirmation()
                ->modalHeading('Confirm Pull from Central Database')
                ->modalDescription('Are you sure you want to pull this client from central database? This will overwrite local data.')
                ->modalSubmitActionLabel('Yes, Pull')
                ->modalIcon('heroicon-o-arrow-down-tray')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (CentralClient $record) {
                    try {
                        DB::transaction(function () use ($record) {
                            // Get data from central database
                            $centralData = $record->toArray();

                            // Handle image transfer if exists
                            if (!empty($centralData['img'])) {
                                $centralImagePath = base_path('../CentralDatabase/storage/app/public/' . $centralData['img']);
                                $localImagePath = storage_path('app/public/' . $centralData['img']);

                                // Ensure local directory exists
                                if (!file_exists(dirname($localImagePath))) {
                                    mkdir(dirname($localImagePath), 0755, true);
                                }

                                // Copy image if exists in central
                                if (file_exists($centralImagePath)) {
                                    copy($centralImagePath, $localImagePath);
                                }
                            }

                            // Remove unwanted fields
                            unset($centralData['id'], $centralData['created_at'], $centralData['updated_at']);

                            // Find or create local client
                            $localClient = Client::where('email', $centralData['email'])
                                ->orWhere('phone', $centralData['phone'])
                                ->first();

                            if ($localClient) {
                                $localClient->update($centralData);
                            } else {
                                $localClient = Client::create($centralData);
                            }

                            // // Sync accounts from central to local
                            // $centralAccounts = $record->accounts;

                            // foreach ($centralAccounts as $centralAccount) {
                            //     $accountData = $centralAccount->toArray();
                            //     unset($accountData['id'], $accountData['created_at'], $accountData['updated_at']);
                            //     $accountData['client_id'] = $localClient->id;

                            //     $localAccount = Account::where('account_number', $accountData['account_number'])
                            //         ->first();

                            //     if ($localAccount) {
                            //         $localAccount->update($accountData);
                            //     } else {
                            //         Account::create($accountData);
                            //     }
                            // }

                            Notification::make()
                                ->title('Client pulled successfully!')
                                ->success()
                                ->send();
                        });
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error pulling client')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
        ];
    }
}
