<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaResource\Pages;
use App\Models\Categoria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoriaResource extends Resource
{
    protected static ?string $model = Categoria::class;
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationLabel = 'Equipamentos';
    protected static ?string $modelLabel = 'Categoria';
    protected static ?string $pluralModelLabel = 'Equipamentos';
    protected static ?string $navigationGroup = 'Logística';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->isAdmin() || $user->isLogistica() || $user->isTecnico());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nome')->required()->label('Nome'),
            Forms\Components\Select::make('tipo')
                ->label('Nível')->options(['departamento'=>'Departamento','familia'=>'Família','subfamilia'=>'SubFamília'])
                ->required()->reactive()->afterStateUpdated(fn ($state, callable $set) => $set('parent_id', null))->default('subfamilia'),
            Forms\Components\Select::make('parent_id')
                ->label(function (callable $get) { return match($get('tipo')) {'familia'=>'Departamento','subfamilia'=>'Família',default=>'Categoria Pai'}; })
                ->options(function (callable $get) {
                    $tipo = $get('tipo');
                    if ($tipo === 'familia') return Categoria::whereNull('parent_id')->pluck('nome','id')->toArray();
                    if ($tipo === 'subfamilia') return Categoria::whereNotNull('parent_id')->whereHas('parent',fn($q)=>$q->whereNull('parent_id'))->pluck('nome','id')->toArray();
                    return [];
                })
                ->nullable()->required(fn ($get)=>$get('tipo')!=='departamento')->visible(fn ($get)=>$get('tipo')!=='departamento')->searchable(),
            Forms\Components\Textarea::make('descricao')->label('Descrição'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationPageOptions([50, 100, 'all'])
            ->columns([
                Tables\Columns\TextColumn::make('dept_nome')->label('Dept.')->width('80px')
                    ->state(function ($record) {
                        if (!$record->parent_id) return strtoupper($record->nome);
                        if ($record->parent && !$record->parent->parent_id) return strtoupper($record->parent->nome);
                        return strtoupper($record->parent->parent->nome ?? '');
                    }),
                Tables\Columns\TextColumn::make('familia_nome')->label('Família')->width('100px')
                    ->state(function ($record) {
                        if ($record->parent_id && !$record->parent->parent_id) return $record->nome;
                        if ($record->parent_id && $record->parent->parent_id) return $record->parent->nome;
                        return '';
                    }),
                Tables\Columns\TextColumn::make('nome')->label('SubFamília')->width('120px')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('nome', 'ilike', "%{$search}%")
                                     ->orWhereHas('equipamentos', fn($q) => $q->where('nome', 'ilike', "%{$search}%"));
                    }),
                Tables\Columns\ViewColumn::make('equipamentos_view')
                    ->label('Equipamentos')
                    ->view('filament.tables.columns.equipamentos-tags'),
                Tables\Columns\TextColumn::make('qtd')->label('Stock')->width('70px')->alignCenter()
                    ->state(function ($record) {
                        $total = $record->equipamentos_sum_quantidade ?? 0;
                        return $total > 0 ? $total : '—';
                    }),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                return $query->with('parent.parent')->with('equipamentos')->withSum('equipamentos', 'quantidade')
                    ->orderByRaw("CASE WHEN parent_id IS NULL THEN id*10000 WHEN (SELECT parent_id FROM categorias p2 WHERE p2.id=categorias.parent_id) IS NULL THEN COALESCE((SELECT id FROM categorias p3 WHERE p3.id=categorias.parent_id),id)*10000+id*100 ELSE COALESCE((SELECT parent_id FROM categorias p4 WHERE p4.id=categorias.parent_id),id)*10000+COALESCE((SELECT id FROM categorias p5 WHERE p5.id=categorias.parent_id),id)*100+id END");
            })
            ->filters([
                Tables\Filters\SelectFilter::make('departamento_filter')->label('Departamento')->options(fn () => Categoria::whereNull('parent_id')->pluck('nome','id')->toArray()),
                Tables\Filters\SelectFilter::make('familia_filter')->label('Família')->options(fn () => Categoria::whereNotNull('parent_id')->whereHas('parent',fn($q)=>$q->whereNull('parent_id'))->pluck('nome','id')->toArray()),
                Tables\Filters\SelectFilter::make('estado_filter')->label('Estado')->options([
                    'disponivel' => '✅ Disponível', 'alugado' => '🚚 Alugado',
                    'manutencao' => '🔧 Manutenção', 'abatido' => '🚫 Abatido',
                ])->query(function (Builder $query, array $data) {
                    if (!empty($data['value'])) {
                        $query->whereHas('equipamentos', fn($q) => $q->where('estado', $data['value']));
                    }
                }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()->label('Editar'),
                    Tables\Actions\Action::make('ver_eq')->label('Ver Equipamentos')->icon('heroicon-o-list-bullet')->color('info')
                        ->url(fn ($record) => route('filament.admin.resources.equipamentos.index', ['tableFilters[categoria_id][value]' => $record->id])),
                    Tables\Actions\Action::make('add_eq')->label('Adicionar Equipamento')->icon('heroicon-o-plus-circle')->color('success')
                        ->url(fn ($record) => route('filament.admin.resources.equipamentos.create', ['categoria_id' => $record->id])),
                    Tables\Actions\Action::make('add_sub')->label('Nova SubFamília')->icon('heroicon-o-folder-plus')->color('warning')
                        ->visible(fn ($record) => !$record->parent_id || ($record->parent_id && !$record->parent->parent_id))
                        ->form([Forms\Components\TextInput::make('nome')->required()->label('Nome')])
                        ->action(function (Categoria $record, array $data): void { Categoria::create(['nome' => $data['nome'], 'parent_id' => $record->id]); }),
                ])->button()->label('Ações')->color('gray'),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array { return []; }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategorias::route('/'),
            'create' => Pages\CreateCategoria::route('/create'),
            'edit' => Pages\EditCategoria::route('/{record}/edit'),
        ];
    }
}
