<?php

namespace App\Filament\Resources\ColaboradorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions;
use Filament\Notifications\Notification;
use App\Models\Equipamento;

class EquipamentosRelationManager extends RelationManager
{
    protected static string $relationship = 'equipamentos';
    protected static ?string $title = 'Equipamentos & Ferramentas';
    protected static ?string $label = 'Item';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('equipamento_id')
                ->label('Item')
                ->options(fn () => Equipamento::orderBy('nome')->pluck('nome', 'id')->toArray())
                ->searchable()
                ->required()
                ->columnSpan(2),
            Forms\Components\Select::make('tipo')
                ->label('Tipo')
                ->options([
                    'Equipamento' => '� Equipamento',
                    'Ferramenta' => '� Ferramenta',
                    'EPI' => '� EPI',
                    'Telemovel' => '� Telemóvel',
                ])
                ->default('Equipamento')
                ->required(),
            Forms\Components\DatePicker::make('data_atribuicao')
                ->label('Data Atribuição')
                ->default(now())
                ->required(),
            Forms\Components\DatePicker::make('data_devolucao')
                ->label('Data Devolução')
                ->nullable(),
            Forms\Components\Textarea::make('notas')
                ->label('Notas')
                ->columnSpanFull(),
        ])->columns(4);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')->searchable()->label('Item'),
                Tables\Columns\TextColumn::make('pivot.tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Equipamento' => 'info',
                        'Ferramenta' => 'warning',
                        'EPI' => 'success',
                        'Telemovel' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('pivot.data_atribuicao')->date()->label('Atribuído em'),
                Tables\Columns\TextColumn::make('pivot.data_devolucao')->date()->label('Devolvido em'),
                Tables\Columns\TextColumn::make('pivot.notas')->label('Notas')->limit(25),
            ])
            ->headerActions([
                Tables\Actions\Action::make('atribuir')
                    ->label('Atribuir Item')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('equipamento_id')
                            ->label('Item')
                            ->options(fn () => Equipamento::orderBy('nome')->pluck('nome', 'id')->toArray())
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('tipo')
                            ->label('Tipo')
                            ->options([
                                'Equipamento' => '� Equipamento',
                                'Ferramenta' => '� Ferramenta',
                                'EPI' => '� EPI',
                                'Telemovel' => '� Telemóvel',
                            ])
                            ->default('Equipamento')
                            ->required(),
                        Forms\Components\DatePicker::make('data_atribuicao')
                            ->label('Data Atribuição')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (array $data, $livewire): void {
                        $owner = $livewire->getOwnerRecord();
                        $owner->equipamentos()->attach($data['equipamento_id'], [
                            'data_atribuicao' => $data['data_atribuicao'],
                            'tipo' => $data['tipo'],
                            'quantidade' => 1,
                        ]);
                        Notification::make()->title('✅ Item atribuído!')->success()->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('devolver')
                    ->label('Devolver')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record, $livewire): void {
                        $owner = $livewire->getOwnerRecord();
                        $owner->equipamentos()->updateExistingPivot($record->id, [
                            'data_devolucao' => now(),
                        ]);
                        Notification::make()->title('✅ Devolvido!')->success()->send();
                    }),
                Tables\Actions\DeleteAction::make()->label('Remover'),
            ]);
    }
}
