<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReparacaoResource\Pages;
use App\Models\Reparacao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class ReparacaoResource extends Resource
{
    protected static ?string $model = Reparacao::class;
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationLabel = 'Reparações';
    protected static ?string $modelLabel = 'Reparação';
    protected static ?string $pluralModelLabel = 'Reparações';
    protected static ?string $navigationGroup = 'Logística';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->isAdmin() || $user->isLogistica() || $user->isTecnico());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('equipamento_id')->relationship('equipamento', 'nome')->required()->label('Equipamento')->searchable(),
            Forms\Components\Textarea::make('descricao_avaria')->required()->label('Descrição da Avaria'),
            Forms\Components\Select::make('estado')->options(['reportado'=>'Reportado','em_reparacao'=>'Em Reparação','resolvido'=>'Resolvido'])->default('reportado')->label('Estado'),
            Forms\Components\TextInput::make('tecnico')->label('Técnico'),
            Forms\Components\TextInput::make('custo_reparacao')->numeric()->prefix('€')->label('Custo'),
            Forms\Components\DatePicker::make('data_entrada')->label('Data Entrada'),
            Forms\Components\DatePicker::make('data_saida')->label('Data Saída'),
            Forms\Components\Textarea::make('notas_tecnicas')->label('Notas Técnicas'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('equipamento.nome')->label('Equipamento')->searchable(),
                Tables\Columns\TextColumn::make('descricao_avaria')->limit(50)->label('Avaria'),
                Tables\Columns\TextColumn::make('estado')->badge()->label('Estado'),
                Tables\Columns\TextColumn::make('tecnico')->label('Técnico'),
                Tables\Columns\TextColumn::make('custo_reparacao')->money('EUR')->label('Custo'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Action::make('pdf')->label('PDF')->icon('heroicon-o-printer')->color('success')->url(fn (Reparacao $record) => route('reparacao.pdf', $record))->openUrlInNewTab()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array { return []; }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReparacaos::route('/'),
            'create' => Pages\CreateReparacao::route('/create'),
            'edit' => Pages\EditReparacao::route('/{record}/edit'),
        ];
    }
}
