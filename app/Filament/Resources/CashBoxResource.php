<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashBoxResource\Pages;
use App\Models\CashBox;
use App\Models\Branch;
use App\Models\CurrencyConfig; // <--- Corrected Model Import
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;

class CashBoxResource extends Resource
{
    protected static ?string $model = CashBox::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $modelLabel = 'Cash Box';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Box Name'),

                Forms\Components\TextInput::make('type')
                    ->label('Type (Safe/Drawer)'),

                Forms\Components\Select::make('currency_id')
                    ->label('Currency')
                    // Corrected to use CurrencyConfig
                    ->options(CurrencyConfig::all()->pluck('currency_type', 'id'))
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('balance')
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),

                Forms\Components\Select::make('branch_id')
                    ->label('Branch')
                    ->options(Branch::all()->pluck('name', 'id'))
                    ->default(auth()->user()->branch_id)
                    ->searchable(),

                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->label('Active Status'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1. ID (Hidden on Mobile)
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->visibleFrom('md'),

                // 2. Name (Editable)
                TextInputColumn::make('name')
                    ->rules(['required', 'max:255'])
                    ->searchable()
                    ->sortable(),

                // 3. Type (Hidden on small mobile)
                TextInputColumn::make('type')
                    ->placeholder('Type...')
                    ->toggleable(isToggledHiddenByDefault: true),

                // 4. Currency (Dropdown using CurrencyConfig)
                SelectColumn::make('currency_id')
                    ->label('Currency')
                    ->options(CurrencyConfig::all()->pluck('currency_type', 'id'))
                    ->selectablePlaceholder(false)
                    ->sortable(),

                // 5. Balance (Editable)
                TextInputColumn::make('balance')
                    ->type('number')
                    ->rules(['numeric'])
                    ->sortable()
                    ->alignCenter(),

                // 6. Branch (Dropdown)
                SelectColumn::make('branch_id')
                    ->label('Branch')
                    ->options(Branch::all()->pluck('name', 'id'))
                    ->visibleFrom('lg')
                    ->sortable(),

                // 7. Active
                ToggleColumn::make('is_active')
                    ->label('Active'),

                // 8. User (Read Only)
                TextColumn::make('user.name')
                    ->label('User')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray'),

                // 9. Date
                TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->options(Branch::all()->pluck('name', 'id')),
                
                // Corrected Filter
                Tables\Filters\SelectFilter::make('currency_id')
                    ->label('Currency')
                    ->options(CurrencyConfig::all()->pluck('currency_type', 'id')),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No Cash Boxes Found')
            ->emptyStateDescription('Create a new cash box to get started.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashBoxes::route('/'),
        ];
    }
}