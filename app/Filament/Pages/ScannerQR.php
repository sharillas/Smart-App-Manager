<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\NumeroSerie;
use App\Models\Equipamento;
use Filament\Notifications\Notification;

class ScannerQR extends Page
{
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationGroup = 'Logística';
    protected static ?string $navigationLabel = 'Scanner QR';
    protected static ?string $title = 'Check-in/out QR Code';
    protected static ?string $slug = 'scanner-qr';
    protected static bool $shouldRegisterNavigation = false;
    
    protected static string $view = 'filament.pages.scanner-qr';
    
    public $qrCode = '';
    public $ultimoScan = null;
    public $historico = [];
    
    public function scanQR()
    {
        $this->qrCode = trim($this->qrCode);
        
        if (empty($this->qrCode)) {
            Notification::make()->warning()->title('Código vazio')->send();
            return;
        }
        
        $ns = NumeroSerie::where('qr_code', $this->qrCode)
            ->orWhere('numero_serie', $this->qrCode)
            ->with('equipamento')
            ->first();
        
        if (!$ns) {
            Notification::make()->danger()->title('QR Code não encontrado')->send();
            return;
        }
        
        $novoEstado = $ns->estado === 'disponivel' ? 'alugado' : 'disponivel';
        $ns->update(['estado' => $novoEstado]);
        
        // Atualizar estado do equipamento baseado nos seus números de série
        $eq = $ns->equipamento;
        $totalAlugados = $eq->numerosSerie()->where('estado', 'alugado')->count();
        $total = $eq->numerosSerie()->count();
        
        if ($totalAlugados == $total) {
            $eq->update(['estado' => 'alugado']);
        } elseif ($totalAlugados == 0) {
            $eq->update(['estado' => 'disponivel']);
        }
        
        $this->ultimoScan = [
            'equipamento' => $eq->nome,
            'numero_serie' => $ns->numero_serie,
            'estado_anterior' => $ns->estado === 'alugado' ? 'disponivel' : 'alugado',
            'estado_novo' => $ns->estado,
            'hora' => now()->format('H:i:s'),
        ];
        
        array_unshift($this->historico, $this->ultimoScan);
        $this->historico = array_slice($this->historico, 0, 10);
        
        $this->qrCode = '';
        
        $icone = $novoEstado === 'alugado' ? '🚚' : '✅';
        $texto = $novoEstado === 'alugado' ? 'CHECK-OUT' : 'CHECK-IN';
        
        Notification::make()
            ->title($icone . ' ' . $texto)
            ->body($eq->nome . ' · S/N: ' . $ns->numero_serie)
            ->color($novoEstado === 'alugado' ? 'warning' : 'success')
            ->send();
    }
}
