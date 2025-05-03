<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Filament\Resources\ClientResource\RelationManagers\AccountRelationManager;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationGroup = 'Customer Management';
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Wizard::make([
                Step::make('Personal Information')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('name')->required(),
                            TextInput::make('email')
                                ->email()
                                ->unique(ignoreRecord: true)
                                ->required(),
                            TextInput::make('phone')
                                ->unique(ignoreRecord: true)
                                ->required(),

                        ]),
                        Grid::make(3)->schema([
                            TextInput::make('citizenship_number')->required(),
                            DatePicker::make('citizenship_issued_date')->required(),
                            TextInput::make('citizenship_issued_place')->required(),
                        ])
                    ]),

                Step::make('Family Details')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('father_name')->required(),
                            TextInput::make('mother_name')->required(),
                            TextInput::make('spouse_name'),
                        ]),
                    ]),

                Step::make('Contact Information')
                    ->schema([
                        Textarea::make('permanent_address')->required(),
                        Textarea::make('temporary_address'),
                    ]),

                Step::make('Others')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('occupation'),
                            TextInput::make('income_source'),
                            TextInput::make('income_range'),
                            Select::make('marital_status')
                                ->options([
                                    'single' => 'Single',
                                    'married' => 'Married',
                                    'divorced' => 'Divorced',
                                    'widowed' => 'Widowed',
                                ])
                                ->nullable(),
                        ]),
                    ]),
            ])->columnSpanFull()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Client Code')->searchable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone'),
                TextColumn::make('citizenship_number'),
                TextColumn::make('citizenship_issued_date')->date(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('Push')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Push to Central Database')
                    ->modalDescription('Are you sure you want to push this client to the central database?')
                    ->modalSubmitActionLabel('Yes, Push')
                    ->modalIcon('heroicon-o-arrow-up-tray')
                    ->icon('heroicon-o-paper-airplane')
                    ->action(function ($record) {
                        $targetConnection = DB::connection('central');
                        $data = $record->toArray();
                        unset($data['id'], $data['created_at'], $data['updated_at']);

                        $existingClient = $targetConnection->table('clients')
                            ->where('email', $data['email'])
                            ->orWhere('phone', $data['phone'])
                            ->first();

                        if ($existingClient) {
                            $targetConnection->table('clients')
                                ->where('id', $existingClient->id)
                                ->update($data);
                        } else {
                            $targetConnection->table('clients')->insert($data);
                            $existingClient = $targetConnection->table('clients')
                                ->where('email', $data['email'])
                                ->orWhere('phone', $data['phone'])
                                ->first();
                        }

                        $record->id = $existingClient->id;
                        $record->save();
                        $record->refresh();


                        //sync accounts
                        // Sync related accounts
                        $accounts = $record->accounts; // assuming a relation: hasMany('App\Models\Account')

                        foreach ($accounts as $account) {
                            $accountData = $account->toArray();
                            unset($accountData['id'], $accountData['created_at'], $accountData['updated_at']);
                            $accountData['client_id'] = $existingClient->id;

                            $existingAccount = $targetConnection->table('accounts')
                                ->where('account_number', $accountData['account_number'])
                                ->first();

                            if ($existingAccount) {
                                $targetConnection->table('accounts')
                                    ->where('id', $existingAccount->id)
                                    ->update($accountData);
                            } else {
                                $targetConnection->table('accounts')->insert($accountData);
                            }
                        }

                        Notification::make()
                            ->title('Client pushed successfully!')
                            ->success()
                            ->send();
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AccountRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
