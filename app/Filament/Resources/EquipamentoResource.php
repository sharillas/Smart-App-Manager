<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipamentoResource\Pages;
use App\Models\Equipamento;
use App\Models\Categoria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Get;

class EquipamentoResource extends Resource
{
    protected static ?string $model = Equipamento::class;
    protected static ?string $navigationIcon = null;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationGroup = 'Logística';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dados do Equipamento')
                ->schema([
                    Forms\Components\TextInput::make('nome')->required()->columnSpanFull()->label('Nome'),
                    Forms\Components\TextInput::make('marca'),
                    Forms\Components\TextInput::make('modelo'),
                    
                    Forms\Components\Select::make('departamento_id')
                        ->label('Departamento')
                        ->options(fn () => Categoria::whereNull('parent_id')->pluck('nome', 'id')->toArray())
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('familia_id', null))
                        ->afterStateUpdated(fn ($state, callable $set) => $set('categoria_id', null))
                        ->searchable()
                        ->required(),
                        
                    Forms\Components\Select::make('familia_id')
                        ->label('Família')
                        ->options(function (Get $get) {
                            $deptId = $get('departamento_id');
                            if (!$deptId) return [];
                            return Categoria::where('parent_id', $deptId)->pluck('nome', 'id')->toArray();
                        })
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('categoria_id', null))
                        ->searchable()
                        ->required(),
                        
                    Forms\Components\Select::make('categoria_id')
                        ->label('SubFamília')
                        ->options(function (Get $get) {
                            $familiaId = $get('familia_id');
                            if (!$familiaId) return [];
                            return Categoria::where('parent_id', $familiaId)->pluck('nome', 'id')->toArray();
                        })
                        ->searchable()
                        ->nullable(),
                        
                    Forms\Components\Select::make('armazem')
                        ->label('Armazém')
                        ->options(['Lisboa' => '📍 Lisboa', 'Porto' => '📍 Porto'])
                        ->nullable(),
                    Forms\Components\TextInput::make('quantidade')->numeric()->default(1)->minValue(1)->label('Stock'),
                ])->columns(3),
                
            Forms\Components\Section::make('Números de Série')
                ->schema([
                    Forms\Components\Repeater::make('numerosSerie')
                        ->relationship()
                        ->schema([
                            Forms\Components\TextInput::make('numero_serie')->label('Nº Série')->required(),
                        ])
                        ->columns(1)->columnSpanFull()
                        ->itemLabel(fn (array $state): ?string => $state['numero_serie'] ?? 'Novo'),
                ]),
                
            Forms\Components\Section::make('Preços e Estado')
                ->schema([
                    Forms\Components\Select::make('estado')
                        ->options([
                            'disponivel' => '✅ Disponível', 'alugado' => '🚚 Alugado',
                            'reservado' => '📅 Reservado', 'manutencao' => '🔧 Manutenção', 'abatido' => '🚫 Abatido',
                        ])->default('disponivel'),
                    Forms\Components\TextInput::make('preco_aluguer_dia')->numeric()->prefix('€')->label('Preço/Dia'),
                    Forms\Components\TextInput::make('preco_custo')->numeric()->prefix('€')->label('Preço Custo'),
                ])->columns(3),
                
            Forms\Components\Textarea::make('notas')->columnSpanFull()->label('Observações'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('marca'),
                Tables\Columns\TextColumn::make('categoria.parent.parent.nome')->label('Departamento')->badge()->color('primary'),
                Tables\Columns\TextColumn::make('categoria.parent.nome')->label('Família'),
                Tables\Columns\TextColumn::make('categoria.nome')->label('SubFamília'),
                Tables\Columns\TextColumn::make('armazem')->label('Armazém')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Lisboa' => 'info', 'Porto' => 'warning', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('quantidade')->label('Qtd'),
                Tables\Columns\TextColumn::make('numeros_serie_count')->counts('numerosSerie')->label('S/N')->alignCenter(),
                Tables\Columns\TextColumn::make('estado')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'disponivel' => 'success', 'alugado' => 'warning',
                        'reservado' => 'info', 'manutencao' => 'danger', 'abatido' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('preco_aluguer_dia')->money('EUR'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')->options([
                    'disponivel' => '✅ Disponível', 'alugado' => '🚚 Alugado',
                    'manutencao' => '🔧 Manutenção', 'abatido' => '🚫 Abatido',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('etiquetas')->label('QR')->icon('heroicon-o-qr-code')->color('primary')
                    ->url(fn ($record) => route('etiquetas.pdf', $record))->openUrlInNewTab()
                    ->visible(fn ($record) => $record->numerosSerie()->count() > 0),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array { return []; }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEquipamentos::route('/'),
            'create' => Pages\CreateEquipamento::route('/create'),
            'edit' => Pages\EditEquipamento::route('/{record}/edit'),
        ];
    }
}
