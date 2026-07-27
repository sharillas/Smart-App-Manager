<?php

use Illuminate\Support\Facades\Route;
use App\Models\Equipamento;
use App\Models\Orcamento;
use App\Models\GuiaTransporte;
use App\Models\Reparacao;
use App\Models\Categoria;
use Barryvdh\DomPDF\Facade\Pdf;

// Redirecionar raiz e /login para o admin
Route::get('/', function () { return redirect('/admin'); });
Route::get('/login', function () { return redirect('/admin/login'); });

// PDFs
Route::get('/orcamento/{orcamento}/pdf', function (Orcamento $orcamento) {
    $pdf = Pdf::loadView('pdf.orcamento', ['orcamento' => $orcamento]);
    return $pdf->download('orcamento-' . $orcamento->numero . '.pdf');
})->name('orcamento.pdf');

Route::get('/guia/{guia}/pdf', function (GuiaTransporte $guia) {
    $pdf = Pdf::loadView('pdf.guia', ['guia' => $guia]);
    return $pdf->download('guia-' . $guia->numero . '.pdf');
})->name('guia.pdf');

Route::get('/reparacao/{reparacao}/pdf', function (Reparacao $reparacao) {
    $pdf = Pdf::loadView('pdf.reparacao', ['reparacao' => $reparacao]);
    return $pdf->download('reparacao-' . $reparacao->id . '.pdf');
})->name('reparacao.pdf');

Route::get('/etiquetas/{equipamento}', function (Equipamento $equipamento) {
    $pdf = Pdf::loadView('pdf.etiquetas', ['equipamento' => $equipamento]);
    return $pdf->download('etiquetas-' . \Illuminate\Support\Str::slug($equipamento->nome) . '.pdf');
})->name('etiquetas.pdf');

Route::get('/export/categorias', function () {
    $categorias = Categoria::with('parent')->get();
    $csv = "ID;Nome;Parent ID;Categoria Pai;Tipo\n";
    foreach ($categorias as $cat) {
        $tipo = !$cat->parent_id ? 'Departamento' : ($cat->parent && !$cat->parent->parent_id ? 'Familia' : 'SubFamilia');
        $csv .= $cat->id . ';' . $cat->nome . ';' . ($cat->parent_id ?? '') . ';' . ($cat->parent->nome ?? '') . ';' . $tipo . "\n";
    }
    return response($csv)->header('Content-Type', 'text/csv; charset=utf-8')->header('Content-Disposition', 'attachment; filename=categorias.csv');
})->name('export.categorias');

Route::get('/export/equipamentos', function () {
    $equipamentos = Equipamento::with(['categoria.parent.parent', 'numerosSerie'])->orderBy('nome')->get();
    $csv = "Nome;Departamento;Familia;SubFamilia;Armazem;Quantidade;Series;PrecoDia\n";
    foreach ($equipamentos as $eq) {
        $cat = $eq->categoria;
        $dept = ''; $familia = ''; $subfamilia = '';
        if ($cat) {
            if ($cat->parent && $cat->parent->parent) { $dept = $cat->parent->parent->nome; $familia = $cat->parent->nome; $subfamilia = $cat->nome; }
            elseif ($cat->parent) { $dept = $cat->parent->nome; $familia = $cat->nome; }
            else { $dept = $cat->nome; }
        }
        $armazem = $eq->armazem ?: '';
        $series = $eq->numerosSerie->pluck('numero_serie')->filter()->implode(' | ');
        $preco = $eq->preco_aluguer_dia ? number_format($eq->preco_aluguer_dia, 2, '.', '') : '0.00';
        $csv .= str_replace(';', ',', $eq->nome) . ';' . $dept . ';' . $familia . ';' . $subfamilia . ';' . $armazem . ';' . $eq->quantidade . ';' . $series . ';' . $preco . "\n";
    }
    return response("\xEF\xBB\xBF" . $csv)->header('Content-Type', 'text/csv; charset=utf-8')->header('Content-Disposition', 'attachment; filename=equipamentos.csv');
})->name('export.equipamentos');
