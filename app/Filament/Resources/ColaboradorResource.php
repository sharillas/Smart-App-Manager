<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ColaboradorResource\Pages;
use App\Filament\Resources\ColaboradorResource\RelationManagers\EquipamentosRelationManager;
use App\Models\Colaborador;
use App\Models\Funcao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ColaboradorResource extends Resource
{
    protected static ?string $model = Colaborador::class;
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationLabel = 'Colaboradores';
    protected static ?string $modelLabel = 'Colaborador';
    protected static ?string $pluralModelLabel = 'Colaboradores';
    protected static ?string $navigationGroup = 'Logística';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->isAdmin() || $user->isLogistica() || $user->isTecnico());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dados Pessoais')
                ->schema([
                    Forms\Components\TextInput::make('nome')->required()->label('Nome Completo'),
                    Forms\Components\Textarea::make('morada')->label('Morada'),
                    Forms\Components\TextInput::make('bi_passaporte')->label('BI / Passaporte'),
                    Forms\Components\TextInput::make('idade')->numeric()->label('Idade'),
                ])->columns(2),
            Forms\Components\Section::make('Dados Profissionais')
                ->schema([
                    Forms\Components\Select::make('funcao')
                        ->label('Função')
                        ->options(fn () => Funcao::where('ativo', true)->orderBy('nome')->pluck('nome', 'nome')->toArray())
                        ->searchable()->required(),
                    Forms\Components\Textarea::make('competencias')->label('Competências'),
                ])->columns(2),
            Forms\Components\Section::make('Segurança')
                ->schema([
                    Forms\Components\Textarea::make('epis')->label('EPI\'s'),
                    Forms\Components\Textarea::make('dados_adicionais')->label('Dados Adicionais')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')->searchable()->label('Nome')->sortable(),
                Tables\Columns\TextColumn::make('funcao')->searchable()->label('Função')->badge()
                    ->color(fn (string $state): string => self::getCorFuncao($state)),
                Tables\Columns\TextColumn::make('itens_lista')->label('Itens Atribuídos')
                    ->state(function ($record) {
                        $itens = $record->equipamentos()->wherePivotNull('data_devolucao')->get();
                        if ($itens->count() === 0) return '—';
                        return $itens->map(function ($item) {
                            $icone = match($item->pivot->tipo) {'Equipamento'=>'💻','Ferramenta'=>'🔧','EPI'=>'🦺','Telemovel'=>'📱',default=>'📦'};
                            return $icone . ' ' . $item->nome;
                        })->implode(', ');
                    })->wrap()->limit(60)
                    ->tooltip(fn ($record) => $record->equipamentos()->wherePivotNull('data_devolucao')->get()->map(fn ($item) => $item->nome . ' (' . \Carbon\Carbon::parse($item->pivot->data_atribuicao)->format('d/m/Y') . ')')->implode("\n")),
                Tables\Columns\TextColumn::make('epis')->label('EPI\'s')->wrap()->limit(30)->tooltip(fn ($record) => $record->epis),
                Tables\Columns\TextColumn::make('competencias')->label('Competências')->wrap()->limit(40)->tooltip(fn ($record) => $record->competencias),
                Tables\Columns\TextColumn::make('bi_passaporte')->label('B.I./Passaporte'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()->label('Remover')])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getCorFuncao(string $funcao): string
    {
        $cores = ['diretor'=>'danger','técnico'=>'info','tecnico'=>'info','motorista'=>'warning','project'=>'success','manager'=>'success','operador'=>'primary','rigger'=>'gray','som'=>'success','luz'=>'warning','vídeo'=>'info','video'=>'info','estrutura'=>'gray','ledwall'=>'primary'];
        $funcaoLower = strtolower($funcao);
        foreach ($cores as $keyword => $cor) { if (str_contains($funcaoLower, $keyword)) return $cor; }
        return 'primary';
    }

    public static function getRelations(): array { return [EquipamentosRelationManager::class]; }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListColaboradores::route('/'),
            'create' => Pages\CreateColaborador::route('/create'),
            'edit' => Pages\EditColaborador::route('/{record}/edit'),
        ];
    }
}
