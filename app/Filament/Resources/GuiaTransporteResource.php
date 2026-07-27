<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuiaTransporteResource\Pages;
use App\Models\GuiaTransporte;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class GuiaTransporteResource extends Resource
{
    protected static ?string $model = GuiaTransporte::class;
    protected static ?string $navigationIcon = null;
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->isAdmin() || $user->isLogistica());
    }

    protected static ?string $navigationGroup = 'Logística';
    protected static ?string $navigationLabel = 'Guias Transporte';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('numero')->required()->unique(ignoreRecord: true)->label('Nº Guia'),
            Forms\Components\Select::make('orcamento_id')->relationship('orcamento', 'numero')->label('Orçamento')->searchable(),
            Forms\Components\Select::make('tipo')->options(['saida' => 'Saída','entrada' => 'Entrada','transferencia' => 'Transferência'])->default('saida'),
            Forms\Components\Select::make('estado')->options(['pendente' => 'Pendente','assinada' => 'Assinada','finalizada' => 'Finalizada'])->default('pendente'),
            Forms\Components\TextInput::make('responsavel')->label('Responsável'),
            Forms\Components\Textarea::make('observacoes')->columnSpanFull(),
            Forms\Components\Repeater::make('itens')->relationship()->schema([
                Forms\Components\Select::make('equipamento_id')->relationship('equipamento', 'nome')->required()->label('Equipamento')->searchable(),
                Forms\Components\TextInput::make('quantidade')->numeric()->default(1)->label('Qtd'),
                Forms\Components\Select::make('estado_saida')->options(['ok' => 'OK','danificado' => 'Danificado'])->label('Estado Saída'),
                Forms\Components\Select::make('estado_retorno')->options(['ok' => 'OK','danificado' => 'Danificado','falta' => 'Falta'])->label('Estado Retorno'),
                Forms\Components\TextInput::make('notas')->label('Notas'),
            ])->columns(5)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero')->searchable()->label('Nº'),
                Tables\Columns\TextColumn::make('orcamento.numero')->label('Orçamento'),
                Tables\Columns\TextColumn::make('tipo')->badge(),
                Tables\Columns\TextColumn::make('estado')->badge(),
                Tables\Columns\TextColumn::make('responsavel'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('pdf')->label('PDF')->icon('heroicon-o-printer')->color('success')
                    ->url(fn (GuiaTransporte $record) => route('guia.pdf', $record))->openUrlInNewTab(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array { return []; }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuiaTransportes::route('/'),
            'create' => Pages\CreateGuiaTransporte::route('/create'),
            'edit' => Pages\EditGuiaTransporte::route('/{record}/edit'),
        ];
    }
}
