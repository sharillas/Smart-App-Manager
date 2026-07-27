<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use App\Models\Orcamento;
use App\Models\Categoria;
use App\Models\Equipamento;
use Smalot\PdfParser\Parser;

class ImportarOrcamento extends Page
{
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationGroup = 'Administração';
    protected static ?string $navigationLabel = 'Importar PDF';
    protected static ?string $title = 'Importar Orçamento PDF';
    protected static ?string $slug = 'importar-pdf';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.importar-orcamento';
    
    public $pdfFile = null;
    public $textoExtraido = '';
    public $dados = [];
    public $itensDetectados = [];
    public $categoriasDetectadas = [];
    
    public function processar()
    {
        $this->validate(['pdfFile' => 'required|file|mimes:pdf|max:10240']);
        
        try {
            $tempPath = sys_get_temp_dir() . '/' . uniqid('pdf_') . '.pdf';
            file_put_contents($tempPath, file_get_contents($this->pdfFile->getRealPath()));
            
            $parser = new Parser();
            $pdf = $parser->parseFile($tempPath);
            $this->textoExtraido = $pdf->getText();
            
            @unlink($tempPath);
            
            $this->extrairDadosWorkPlanit();
            
            Notification::make()
                ->title('✅ PDF processado!')
                ->body(count($this->itensDetectados) . ' itens em ' . count($this->categoriasDetectadas) . ' categorias')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    private function extrairDadosWorkPlanit()
    {
        $texto = $this->textoExtraido;
        $linhas = explode("\n", $texto);
        
        // Cabeçalho
        preg_match('/ORÇAMENTO\s*\n?\s*(PR_[A-Za-z0-9_\-]+)/', $texto, $matches);
        $this->dados['numero'] = $matches[1] ?? 'IMP-' . date('Ymd');
        
        preg_match('/Nome Cliente:\s*\n?\s*(.+?)\n/', $texto, $matches);
        $this->dados['cliente_nome'] = trim($matches[1] ?? '');
        
        preg_match('/Local do Evento:\s*\n?\s*(.+?)\n/', $texto, $matches);
        $this->dados['evento_local'] = trim($matches[1] ?? '');
        
        preg_match('/PROJETO\s*\n\s*(.+?)\n/', $texto, $matches);
        $this->dados['evento_nome'] = trim($matches[1] ?? '');
        
        preg_match('/Data inicio:\s*(\d{2}\/\d{2}\/\d{4})/', $texto, $matches);
        if (isset($matches[1])) {
            $this->dados['data_inicio'] = \Carbon\Carbon::createFromFormat('d/m/Y', $matches[1])->format('Y-m-d');
        }
        
        preg_match('/Data Fim:\s*(\d{2}\/\d{2}\/\d{4})/', $texto, $matches);
        if (isset($matches[1])) {
            $this->dados['data_fim'] = \Carbon\Carbon::createFromFormat('d/m/Y', $matches[1])->format('Y-m-d');
        }
        
        // Itens - procurar padrão: "Tenda" + nome + unidade + quantidade
        $this->itensDetectados = [];
        $this->categoriasDetectadas = [];
        $categoriaAtual = 'Geral';
        
        // Padrão mais flexível: procurar linhas com "Tenda" seguidas de nome, Un/m2/m e número
        for ($i = 0; $i < count($linhas) - 3; $i++) {
            $l0 = trim($linhas[$i] ?? '');
            $l1 = trim($linhas[$i+1] ?? '');
            $l2 = trim($linhas[$i+2] ?? '');
            $l3 = trim($linhas[$i+3] ?? '');
            
            // Detetar categoria
            if (preg_match('/^(Áudio|Audio|Vídeo|Video|Iluminação|Iluminacao|Estruturas|Mobiliário|Mobiliario|Cenografia|Informática|Informatica|Técnico|Tecnico|Transporte)\s*\/?\s*/i', $l0, $m)) {
                $categoriaAtual = ucfirst(strtolower($m[1]));
                if (!in_array($categoriaAtual, $this->categoriasDetectadas)) {
                    $this->categoriasDetectadas[] = $categoriaAtual;
                }
            }
            
            // Detetar item: linha "Tenda" + nome + unidade + qtd
            if ($l0 === 'Tenda' && strlen($l1) > 3 && in_array($l2, ['Un', 'm2', 'm', 'Tile'])) {
                $qtd = floatval(str_replace(',', '.', $l3));
                
                if ($qtd > 0 && $l1 !== 'DESCRIÇÃO' && $l1 !== 'ART.') {
                    $this->itensDetectados[] = [
                        'categoria' => $categoriaAtual,
                        'nome' => $l1,
                        'quantidade' => $qtd,
                        'preco_unitario' => 0,
                        'subtotal' => 0,
                    ];
                }
            }
        }
    }
    
    public function criarOrcamento()
    {
        if (empty($this->dados['numero'])) {
            Notification::make()->title('Processe o PDF primeiro')->warning()->send();
            return;
        }
        
        $orcamento = Orcamento::create([
            'numero' => $this->dados['numero'],
            'cliente_nome' => $this->dados['cliente_nome'] ?: 'Importado',
            'evento_nome' => $this->dados['evento_nome'] ?: '',
            'evento_local' => $this->dados['evento_local'] ?: '',
            'data_inicio' => $this->dados['data_inicio'] ?? now()->format('Y-m-d'),
            'data_fim' => $this->dados['data_fim'] ?? now()->addDays(1)->format('Y-m-d'),
            'estado' => 'orcamentacao',
            'valor_total' => 0,
        ]);
        
        $total = 0;
        foreach ($this->itensDetectados as $item) {
            $equipamento = Equipamento::where('nome', 'ilike', '%' . substr($item['nome'], 0, 15) . '%')->first();
            
            $orcamento->itens()->create([
                'equipamento_id' => $equipamento?->id,
                'quantidade' => $item['quantidade'],
                'preco_unitario' => $equipamento?->preco_aluguer_dia ?? 0,
                'dias' => 1,
                'subtotal' => ($equipamento?->preco_aluguer_dia ?? 0) * $item['quantidade'],
            ]);
            
            $total += ($equipamento?->preco_aluguer_dia ?? 0) * $item['quantidade'];
        }
        
        $orcamento->update(['valor_total' => $total]);
        
        Notification::make()
            ->title('✅ Orçamento criado!')
            ->body($orcamento->numero . ' · ' . count($this->itensDetectados) . ' itens')
            ->success()
            ->send();
        
        return redirect()->route('filament.admin.resources.orcamentos.edit', $orcamento);
    }
}
