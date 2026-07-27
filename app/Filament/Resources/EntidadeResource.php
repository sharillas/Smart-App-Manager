<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EntidadeResource\Pages;
use App\Models\Entidade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EntidadeResource extends Resource
{
    protected static ?string $model = Entidade::class;
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationLabel = 'Entidades';
    protected static ?string $modelLabel = 'Entidade';
    protected static ?string $pluralModelLabel = 'Entidades';
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->isAdmin() || $user->isComercial());
    }

    protected static ?string $navigationGroup = 'Comercial';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dados da Entidade')
                ->schema([
                    Forms\Components\TextInput::make('nome')->required()->label('Nome')->columnSpanFull(),
                    Forms\Components\TextInput::make('designacao_comercial')->label('Designação Comercial'),
                    Forms\Components\Select::make('tipo_entidade')->options([
                        'Cliente' => 'Cliente', 'Fornecedor' => 'Fornecedor',
                        'Parceiro' => 'Parceiro', 'Outro' => 'Outro',
                    ])->default('Cliente')->required()->label('Tipo'),
                    Forms\Components\TextInput::make('nif')->label('NIF'),
                    Forms\Components\TextInput::make('pais')->default('Portugal')->label('País'),
                ])->columns(3),
            Forms\Components\Section::make('Contactos')
                ->schema([
                    Forms\Components\TextInput::make('email')->email()->label('Email'),
                    Forms\Components\TextInput::make('telefone')->label('Telefone'),
                    Forms\Components\Textarea::make('morada')->label('Morada')->columnSpanFull(),
                ])->columns(2),
            Forms\Components\Textarea::make('notas')->label('Notas')->columnSpanFull(),
            Forms\Components\Toggle::make('ativo')->default(true)->label('Ativo'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationPageOptions([25, 50, 100])
            ->columns([
                Tables\Columns\TextColumn::make('nome')->searchable()->sortable()->label('Nome'),
                Tables\Columns\TextColumn::make('designacao_comercial')->searchable()->label('Designação'),
                Tables\Columns\TextColumn::make('tipo_entidade')->badge()->color(fn ($state) => match($state) {
                    'Cliente' => 'success', 'Fornecedor' => 'warning', 'Parceiro' => 'info', default => 'gray',
                })->label('Tipo'),
                Tables\Columns\TextColumn::make('nif')->searchable()->label('NIF'),
                Tables\Columns\TextColumn::make('pais')->label('País'),
                Tables\Columns\IconColumn::make('ativo')->boolean()->label('Ativo'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo_entidade')->label('Tipo')->options([
                    'Cliente' => 'Cliente', 'Fornecedor' => 'Fornecedor', 'Parceiro' => 'Parceiro', 'Outro' => 'Outro',
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array { return []; }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEntidades::route('/'),
            'create' => Pages\CreateEntidade::route('/create'),
            'edit' => Pages\EditEntidade::route('/{record}/edit'),
        ];
    }
}
