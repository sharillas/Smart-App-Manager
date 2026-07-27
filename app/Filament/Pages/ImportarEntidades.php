<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use App\Models\Entidade;

class ImportarEntidades extends Page
{
    protected static ?string $navigationIcon = null;
    protected static ?string $title = 'Importar Entidades';
    protected static ?string $slug = 'importar-entidades';
    protected static string $view = 'filament.pages.importar-entidades';
    protected static bool $shouldRegisterNavigation = false;
    
    public $ficheiro;
    public $preview = [];
    public $totalLinhas = 0;
    
    public function processar()
    {
        $this->validate(['ficheiro' => 'required|file|mimes:xlsx,csv']);
        
        try {
            $data = \Maatwebsite\Excel\Facades\Excel::toCollection(null, $this->ficheiro->getRealPath());
            $rows = $data->first();
            $this->totalLinhas = count($rows) - 1;
            $this->preview = $rows->take(11)->toArray();
            
            Notification::make()->title('✅ Ficheiro carregado!')->body($this->totalLinhas . ' entidades')->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title('Erro')->body($e->getMessage())->danger()->send();
        }
    }
    
    public function importar()
    {
        try {
            $data = \Maatwebsite\Excel\Facades\Excel::toCollection(null, $this->ficheiro->getRealPath());
            $rows = $data->first();
            $importados = 0;
            
            foreach ($rows->skip(1) as $row) {
                $nome = trim($row[0] ?? '');
                if (empty($nome)) continue;
                
                Entidade::create([
                    'nome' => $nome,
                    'designacao_comercial' => trim($row[1] ?? ''),
                    'tipo_entidade' => trim($row[2] ?? 'Cliente') ?: 'Cliente',
                    'nif' => trim($row[3] ?? ''),
                    'pais' => trim($row[4] ?? 'Portugal') ?: 'Portugal',
                ]);
                $importados++;
            }
            
            Notification::make()->title('✅ Importação concluída!')->body($importados . ' entidades importadas.')->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title('Erro')->body($e->getMessage())->danger()->send();
        }
    }
}
