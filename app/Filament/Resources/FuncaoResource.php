<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FuncaoResource\Pages;
use App\Models\Funcao;
use App\Models\Categoria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FuncaoResource extends Resource
{
    protected static ?string $model = Funcao::class;
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationLabel = 'Funções';
    protected static ?string $modelLabel = 'Função';
    protected static ?string $pluralModelLabel = 'Funções';
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->isAdmin() || $user->isLogistica());
    }

    protected static ?string $navigationGroup = 'Logística';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nome')->required()->label('Nome da Função'),
            Forms\Components\Select::make('departamento_id')
                ->label('Departamento')
                ->options(fn () => Categoria::whereNull('parent_id')->pluck('nome', 'id')->toArray())
                ->searchable()
                ->nullable(),
            Forms\Components\Textarea::make('descricao')->label('Descrição'),
            Forms\Components\Toggle::make('ativo')->default(true)->label('Ativo'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')->searchable()->sortable()->label('Função'),
                Tables\Columns\TextColumn::make('departamento.nome')
                    ->label('Departamento')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'Áudio' => 'success',
                        'Vídeo' => 'info',
                        'Estruturas' => 'warning',
                        'Iluminação' => 'danger',
                        'Mobiliário' => 'gray',
                        'Tradução Simultânea' => 'primary',
                        'Transporte' => 'warning',
                        'Administração' => 'gray',
                        'Administrativo' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('ativo')->boolean()->label('Ativo'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array { return []; }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFuncoes::route('/'),
            'create' => Pages\CreateFuncao::route('/create'),
            'edit' => Pages\EditFuncao::route('/{record}/edit'),
        ];
    }
}
