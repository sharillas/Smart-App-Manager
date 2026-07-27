<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use App\Models\Equipamento;
use App\Models\Categoria;
use App\Models\NumeroSerie;

class ImportarEquipamentos extends Page
{
    protected static ?string $navigationIcon = null;
    protected static ?string $title = 'Importar Equipamentos';
    protected static ?string $slug = 'importar-equipamentos';
    protected static string $view = 'filament.pages.importar-equipamentos';
    protected static bool $shouldRegisterNavigation = false;
    
    public $ficheiro;
    public $preview = [];
    public $totalLinhas = 0;
    public $colunasDetectadas = [];
    
    public function processar()
    {
        $this->validate(['ficheiro' => 'required|file|mimes:xlsx,csv']);
        
        try {
            $data = \Maatwebsite\Excel\Facades\Excel::toCollection(null, $this->ficheiro->getRealPath());
            $rows = $data->first();
            $this->totalLinhas = count($rows) - 1;
            $this->preview = $rows->take(11)->toArray();
            
            if (count($rows) > 0) {
                $this->colunasDetectadas = $rows[0]->toArray();
            }
            
            Notification::make()->title('✅ Ficheiro carregado!')->body($this->totalLinhas . ' linhas')->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title('Erro')->body($e->getMessage())->danger()->send();
        }
    }
    
    public function importar()
    {
        try {
            $data = \Maatwebsite\Excel\Facades\Excel::toCollection(null, $this->ficheiro->getRealPath());
            $rows = $data->first();
            $cabecalho = $rows->first()->toArray();
            
            // Mapear colunas
            $mapaColunas = [];
            foreach ($cabecalho as $index => $nome) {
                $nomeLower = strtolower(trim($nome));
                if (str_contains($nomeLower, 'nome') && !str_contains($nomeLower, 'departamento') && !str_contains($nomeLower, 'familia') && !str_contains($nomeLower, 'sub')) $mapaColunas['nome'] = $index;
                if (str_contains($nomeLower, 'departamento') || str_contains($nomeLower, 'dept')) $mapaColunas['departamento'] = $index;
                if (str_contains($nomeLower, 'familia') && !str_contains($nomeLower, 'sub')) $mapaColunas['familia'] = $index;
                if (str_contains($nomeLower, 'subfamilia') || str_contains($nomeLower, 'sub')) $mapaColunas['subfamilia'] = $index;
                if (str_contains($nomeLower, 'armazem')) $mapaColunas['armazem'] = $index;
                if (str_contains($nomeLower, 'quantidade') || str_contains($nomeLower, 'qtd') || str_contains($nomeLower, 'stock')) $mapaColunas['quantidade'] = $index;
                if (str_contains($nomeLower, 'serie') || str_contains($nomeLower, 's/n')) $mapaColunas['series'] = $index;
                if (str_contains($nomeLower, 'preco') || str_contains($nomeLower, 'preço')) $mapaColunas['preco'] = $index;
            }
            
            $importados = 0;
            $erros = 0;
            
            foreach ($rows->skip(1) as $linha => $row) {
                try {
                    $nome = trim($row[$mapaColunas['nome']] ?? '');
                    if (empty($nome)) continue;
                    
                    $deptNome = trim($row[$mapaColunas['departamento']] ?? '');
                    $familiaNome = trim($row[$mapaColunas['familia']] ?? '');
                    $subfamiliaNome = trim($row[$mapaColunas['subfamilia']] ?? '');
                    $armazem = trim($row[$mapaColunas['armazem']] ?? '');
                    $quantidade = intval($row[$mapaColunas['quantidade']] ?? 1);
                    $seriesTexto = trim($row[$mapaColunas['series']] ?? '');
                    $preco = floatval(str_replace(',', '.', $row[$mapaColunas['preco']] ?? 0));
                    
                    $categoriaId = null;
                    
                    // 1. Departamento (CRIA se não existir)
                    if (!empty($deptNome)) {
                        $dept = Categoria::firstOrCreate(
                            ['nome' => $deptNome, 'parent_id' => null],
                            ['descricao' => 'Importado via Excel']
                        );
                        
                        // 2. Família (CRIA se não existir, dentro do departamento)
                        if (!empty($familiaNome)) {
                            $familia = Categoria::firstOrCreate(
                                ['nome' => $familiaNome, 'parent_id' => $dept->id],
                                ['descricao' => 'Importado via Excel']
                            );
                            
                            // 3. SubFamília (CRIA se não existir, dentro da família)
                            if (!empty($subfamiliaNome)) {
                                $subfamilia = Categoria::firstOrCreate(
                                    ['nome' => $subfamiliaNome, 'parent_id' => $familia->id],
                                    ['descricao' => 'Importado via Excel']
                                );
                                $categoriaId = $subfamilia->id;
                            } else {
                                $categoriaId = $familia->id;
                            }
                        } else {
                            $categoriaId = $dept->id;
                        }
                    }
                    // Se não tem departamento, deixa categoriaId = null (sem categoria)
                    
                    // Criar equipamento
                    $eq = Equipamento::create([
                        'nome' => $nome,
                        'categoria_id' => $categoriaId,
                        'quantidade' => max(1, $quantidade),
                        'armazem' => $armazem ?: null,
                        'preco_aluguer_dia' => $preco > 0 ? $preco : null,
                        'estado' => 'disponivel',
                    ]);
                    
                    // Importar números de série (separados por |)
                    if (!empty($seriesTexto) && $seriesTexto !== '—' && $seriesTexto !== '-') {
                        $series = array_map('trim', explode('|', $seriesTexto));
                        foreach ($series as $serie) {
                            if (!empty($serie)) {
                                NumeroSerie::create([
                                    'equipamento_id' => $eq->id,
                                    'numero_serie' => $serie,
                                ]);
                            }
                        }
                    }
                    
                    $importados++;
                } catch (\Exception $e) {
                    $erros++;
                }
            }
            
            Notification::make()
                ->title('✅ Importação concluída!')
                ->body($importados . ' equipamentos importados. ' . $erros . ' erros.')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()->title('Erro')->body($e->getMessage())->danger()->send();
        }
    }
}
