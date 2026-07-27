<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrcamentoResource\Pages;
use App\Models\Orcamento;
use App\Models\Equipamento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class OrcamentoResource extends Resource
{
    protected static ?string $model = Orcamento::class;
    protected static ?string $navigationIcon = null;
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->isAdmin() || $user->isComercial());
    }

    protected static ?string $navigationGroup = 'Comercial';
    protected static ?string $navigationLabel = 'Orçamentos';
    protected static ?string $modelLabel = 'Orçamento';
    protected static ?string $pluralModelLabel = 'Orçamentos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados do Cliente e Evento')
                    ->schema([
                        Forms\Components\TextInput::make('numero')->required()->unique(ignoreRecord: true)->label('Nº Orçamento'),
                        Forms\Components\TextInput::make('cliente_nome')->required()->label('Cliente'),
                        Forms\Components\TextInput::make('evento_nome')->label('Evento'),
                        Forms\Components\TextInput::make('evento_local')->label('Local'),
                        Forms\Components\DatePicker::make('data_inicio')->required()->label('Início'),
                        Forms\Components\DatePicker::make('data_fim')->required()->label('Fim'),
                    ])->columns(3),
                Forms\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Select::make('estado')->options([
                            'orcamentacao' => 'Orçamentação', 'draft' => 'Draft',
                            'confirmado' => 'Confirmado', 'cancelado' => 'Cancelado',
                        ])->default('orcamentacao')->required()->label('Estado'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero')->searchable()->label('Nº')->sortable(),
                Tables\Columns\TextColumn::make('cliente_nome')->searchable()->label('Cliente'),
                Tables\Columns\TextColumn::make('evento_nome')->label('Evento'),
                Tables\Columns\TextColumn::make('data_inicio')->date()->label('Início'),
                Tables\Columns\TextColumn::make('data_fim')->date()->label('Fim'),
                Tables\Columns\TextColumn::make('estado')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'orcamentacao' => 'warning', 'draft' => 'primary',
                        'confirmado' => 'success', 'cancelado' => 'danger', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('valor_total')->money('EUR')->label('Total'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')->options([
                    'orcamentacao' => '🟡 Orçamentação', 'draft' => '🔵 Draft',
                    'confirmado' => '🟢 Confirmado', 'cancelado' => '🔴 Cancelado',
                ]),
            ])
            ->actions([
                // EDITAR vai para a página avançada
                Action::make('editar_avancado')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil')
                    ->color('primary')
                    ->url(fn (Orcamento $record) => route('filament.admin.pages.orcamento-avancado', ['record' => $record->id])),
                Action::make('pdf')->label('PDF')->icon('heroicon-o-printer')->color('success')
                    ->url(fn (Orcamento $record) => route('orcamento.pdf', $record))->openUrlInNewTab(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array { return []; }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrcamentos::route('/'),
            'create' => Pages\CreateOrcamento::route('/create'),
            'edit' => Pages\EditOrcamento::route('/{record}/edit'),
        ];
    }
}
