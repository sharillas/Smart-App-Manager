<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationLabel = 'Users App';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'Users App';
    protected static ?string $navigationGroup = 'App Admin';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && $user->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->label('Nome'),
            Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true)->label('Email'),
            Forms\Components\TextInput::make('password')
                ->password()->label('Password')
                ->dehydrateStateUsing(fn ($state) => !empty($state) ? Hash::make($state) : null)
                ->dehydrated(fn ($state) => !empty($state))
                ->helperText('Deixe em branco para manter a atual'),
            Forms\Components\Select::make('role')->options([
                'admin' => '🔑 Admin (Tudo)',
                'comercial' => '💰 Comercial (Orçamentos + Entidades)',
                'logistica' => '🚛 Logística (Equipamentos + Colaboradores + Guias)',
                'tecnico' => '🔧 Técnico (Ver equipamentos + Colaboradores + Reparações)',
                'visualizador' => '👁️ Visualizador (Só ver)',
            ])->default('visualizador')->required()->label('Função'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->label('Nome'),
                Tables\Columns\TextColumn::make('email')->searchable()->label('Email'),
                Tables\Columns\TextColumn::make('role')->badge()->color(fn (string $state): string => match ($state) {
                    'admin' => 'success', 'comercial' => 'warning', 'logistica' => 'info', 'tecnico' => 'danger', 'visualizador' => 'gray',
                })->formatStateUsing(fn (string $state): string => match ($state) {
                    'admin' => 'Admin', 'comercial' => 'Comercial', 'logistica' => 'Logística', 'tecnico' => 'Técnico', 'visualizador' => 'Visualizador',
                })->label('Função'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array { return []; }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
