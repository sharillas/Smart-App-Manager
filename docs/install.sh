#!/bin/bash
# =============================================
# SMARTCHOICE EVENT MANAGER v1.7
# Script de Instalação - Baseado na VPS de Produção
# Ubuntu 22.04/24.04 + PHP 8.2/8.3 + SQLite
# =============================================
set -e
clear
cat << "BANNER"
╔══════════════════════════════════════════════╗
║  🎪 SMARTCHOICE EVENT MANAGER v1.7          ║
║  Instalação Completa                         ║
╚══════════════════════════════════════════════╝
BANNER

source /etc/os-release
PHP_VERSION="8.2"; [ "$VERSION_ID" = "24.04" ] && PHP_VERSION="8.3"
PHP_SOCK="/var/run/php/php${PHP_VERSION}-fpm.sock"
IP=$(hostname -I | awk '{print $1}')
ADMIN_EMAIL="admin@admin.com"
ADMIN_PASS="Admin123!"
echo "📋 Ubuntu ${VERSION_ID} → PHP ${PHP_VERSION} | IP: ${IP}"
sleep 2

echo "[1/10] Sistema + PHP + Composer"
apt update -y -qq && apt upgrade -y -qq
apt install -y -qq curl wget git unzip zip cron ufw nginx
curl -sSLo /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg
sh -c "echo 'deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main' > /etc/apt/sources.list.d/php.list"
apt update -y -qq
apt install -y -qq php${PHP_VERSION} php${PHP_VERSION}-fpm php${PHP_VERSION}-cli php${PHP_VERSION}-sqlite3 php${PHP_VERSION}-curl php${PHP_VERSION}-gd php${PHP_VERSION}-mbstring php${PHP_VERSION}-xml php${PHP_VERSION}-zip php${PHP_VERSION}-bcmath php${PHP_VERSION}-intl php${PHP_VERSION}-opcache
sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/' /etc/php/${PHP_VERSION}/fpm/php.ini
sed -i 's/memory_limit = 128M/memory_limit = 256M/' /etc/php/${PHP_VERSION}/fpm/php.ini
systemctl enable php${PHP_VERSION}-fpm --now
export COMPOSER_ALLOW_SUPERUSER=1
php -r "copy('https://getcomposer.org/installer','composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer --quiet
php -r "unlink('composer-setup.php');"
echo "✅"

echo "[2/10] Laravel + Filament + Dependências"
cd /var/www && rm -rf gestao-eventos 2>/dev/null
composer create-project laravel/laravel gestao-eventos --no-interaction --prefer-dist --quiet
cd gestao-eventos
cat > .env << ENVEOF
APP_NAME="Smartchoice Event Manager"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://${IP}
APP_KEY=
DB_CONNECTION=sqlite
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
ENVEOF
php artisan key:generate --force --quiet
touch database/database.sqlite
composer require filament/filament:"^3.0" -W --no-interaction --prefer-dist --quiet
composer require barryvdh/laravel-dompdf --no-interaction --quiet
composer require simplesoftwareio/simple-qrcode --no-interaction --quiet
composer require smalot/pdfparser --no-interaction --quiet
composer require maatwebsite/excel --no-interaction --quiet
php artisan filament:install --panels --no-interaction --quiet
echo "✅"
echo "[3/10] Base de dados (20 tabelas)"
rm -f database/database.sqlite && touch database/database.sqlite
php artisan tinker --execute="
Schema::create('users',function(\$t){\$t->id();\$t->string('name');\$t->string('email')->unique();\$t->timestamp('email_verified_at')->nullable();\$t->string('password');\$t->string('role')->default('visualizador');\$t->rememberToken();\$t->timestamps();});
Schema::create('cache',function(\$t){\$t->string('key')->primary();\$t->mediumText('value');\$t->integer('expiration');});
Schema::create('cache_locks',function(\$t){\$t->string('key')->primary();\$t->string('owner');\$t->integer('expiration');});
Schema::create('sessions',function(\$t){\$t->string('id')->primary();\$t->foreignId('user_id')->nullable()->index();\$t->string('ip_address',45)->nullable();\$t->text('user_agent')->nullable();\$t->longText('payload');\$t->integer('last_activity')->index();});
Schema::create('jobs',function(\$t){\$t->bigIncrements('id');\$t->string('queue')->index();\$t->longText('payload');\$t->unsignedTinyInteger('attempts');\$t->unsignedInteger('reserved_at')->nullable();\$t->unsignedInteger('available_at');\$t->unsignedInteger('created_at');});
Schema::create('categorias',function(\$t){\$t->id();\$t->string('nome');\$t->foreignId('parent_id')->nullable()->constrained('categorias')->onDelete('cascade');\$t->text('descricao')->nullable();\$t->integer('ordem')->default(0);\$t->timestamps();});
Schema::create('equipamentos',function(\$t){\$t->id();\$t->string('nome');\$t->string('marca')->nullable();\$t->string('modelo')->nullable();\$t->foreignId('categoria_id')->nullable()->constrained('categorias')->onDelete('set null');\$t->string('estado')->default('disponivel');\$t->integer('quantidade')->default(1);\$t->decimal('preco_aluguer_dia',10,2)->nullable();\$t->decimal('preco_custo',10,2)->nullable();\$t->string('armazem')->nullable();\$t->text('notas')->nullable();\$t->timestamps();});
Schema::create('numeros_serie',function(\$t){\$t->id();\$t->foreignId('equipamento_id')->constrained('equipamentos')->onDelete('cascade');\$t->string('numero_serie');\$t->string('qr_code')->unique()->nullable();\$t->string('estado')->default('disponivel');\$t->timestamps();});
Schema::create('orcamentos',function(\$t){\$t->id();\$t->string('numero')->unique();\$t->string('cliente_nome');\$t->string('cliente_email')->nullable();\$t->string('cliente_telefone')->nullable();\$t->string('evento_nome')->nullable();\$t->string('evento_local')->nullable();\$t->date('data_inicio');\$t->date('data_fim');\$t->string('estado')->default('orcamentacao');\$t->decimal('valor_total',10,2)->default(0);\$t->text('notas')->nullable();\$t->timestamps();});
Schema::create('orcamento_itens',function(\$t){\$t->id();\$t->foreignId('orcamento_id')->constrained('orcamentos')->onDelete('cascade');\$t->foreignId('equipamento_id')->nullable()->constrained('equipamentos')->onDelete('set null');\$t->integer('quantidade')->default(1);\$t->decimal('preco_unitario',10,2)->default(0);\$t->integer('dias')->default(1);\$t->decimal('subtotal',10,2)->default(0);\$t->boolean('subaluguer')->default(false);\$t->string('fornecedor')->nullable();\$t->decimal('custo_subaluguer',10,2)->nullable();\$t->timestamps();});
Schema::create('guia_transportes',function(\$t){\$t->id();\$t->string('numero')->unique();\$t->foreignId('orcamento_id')->nullable()->constrained('orcamentos')->onDelete('set null');\$t->string('tipo')->default('carga');\$t->string('estado')->default('pendente');\$t->string('responsavel')->nullable();\$t->text('observacoes')->nullable();\$t->timestamps();});
Schema::create('guia_itens',function(\$t){\$t->id();\$t->foreignId('guia_transporte_id')->constrained('guia_transportes')->onDelete('cascade');\$t->foreignId('equipamento_id')->nullable()->constrained('equipamentos')->onDelete('set null');\$t->integer('quantidade')->default(1);\$t->text('notas')->nullable();\$t->timestamps();});
Schema::create('reparacoes',function(\$t){\$t->id();\$t->foreignId('equipamento_id')->constrained('equipamentos')->onDelete('cascade');\$t->text('descricao_avaria');\$t->string('estado')->default('reportado');\$t->string('tecnico')->nullable();\$t->decimal('custo_reparacao',10,2)->nullable();\$t->date('data_entrada')->nullable();\$t->date('data_saida')->nullable();\$t->text('notas_tecnicas')->nullable();\$t->timestamps();});
Schema::create('colaboradores',function(\$t){\$t->id();\$t->string('nome');\$t->string('morada')->nullable();\$t->string('bi_passaporte')->nullable();\$t->string('funcao')->nullable();\$t->text('competencias')->nullable();\$t->integer('idade')->nullable();\$t->text('epis')->nullable();\$t->text('dados_adicionais')->nullable();\$t->timestamps();});
Schema::create('colaborador_equipamento',function(\$t){\$t->id();\$t->foreignId('colaborador_id')->constrained('colaboradores')->onDelete('cascade');\$t->foreignId('equipamento_id')->constrained('equipamentos')->onDelete('cascade');\$t->integer('quantidade')->default(1);\$t->date('data_atribuicao')->nullable();\$t->date('data_devolucao')->nullable();\$t->text('notas')->nullable();\$t->string('tipo')->default('Equipamento');\$t->foreignId('numero_serie_id')->nullable()->constrained('numeros_serie')->onDelete('set null');\$t->timestamps();});
Schema::create('entidades',function(\$t){\$t->id();\$t->string('nome');\$t->string('designacao_comercial')->nullable();\$t->string('tipo_entidade')->default('Cliente');\$t->string('nif')->nullable();\$t->string('pais')->default('Portugal');\$t->string('email')->nullable();\$t->string('telefone')->nullable();\$t->text('morada')->nullable();\$t->text('notas')->nullable();\$t->boolean('ativo')->default(true);\$t->timestamps();});
Schema::create('funcoes',function(\$t){\$t->id();\$t->string('nome');\$t->foreignId('departamento_id')->nullable()->constrained('categorias')->onDelete('set null');\$t->text('descricao')->nullable();\$t->boolean('ativo')->default(true);\$t->timestamps();});
Schema::create('logs_stock',function(\$t){\$t->id();\$t->foreignId('orcamento_id')->constrained('orcamentos')->onDelete('cascade');\$t->foreignId('equipamento_id')->constrained('equipamentos')->onDelete('cascade');\$t->integer('quantidade_antes');\$t->integer('quantidade_depois');\$t->string('acao');\$t->timestamps();});
Schema::create('escalas_tecnicos',function(\$t){\$t->id();\$t->foreignId('colaborador_id')->constrained('colaboradores')->onDelete('cascade');\$t->foreignId('orcamento_id')->nullable()->constrained('orcamentos')->onDelete('cascade');\$t->date('data_inicio');\$t->date('data_fim');\$t->time('hora_entrada')->nullable();\$t->time('hora_saida')->nullable();\$t->string('funcao')->nullable();\$t->text('notas')->nullable();\$t->timestamps();});
echo 'OK';
" --quiet
echo "✅"
echo "[4/10] Modelos + Admin + Observer"

# User
cat > app/Models/User.php << 'EOF'
<?php
namespace App\Models;
use Filament\Models\Contracts\FilamentUser;use Filament\Panel;use Illuminate\Foundation\Auth\User as Authenticatable;use Illuminate\Notifications\Notifiable;
class User extends Authenticatable implements FilamentUser{
use Notifiable;protected $fillable=['name','email','password','role'];protected $hidden=['password','remember_token'];
protected function casts():array{return['email_verified_at'=>'datetime','password'=>'hashed'];}
public function canAccessPanel(Panel $panel):bool{return true;}
public function isAdmin():bool{return $this->role==='admin';}
public function isComercial():bool{return $this->role==='comercial';}
public function isLogistica():bool{return $this->role==='logistica';}
public function isTecnico():bool{return $this->role==='tecnico';}
public function isVisualizador():bool{return $this->role==='visualizador';}}
EOF

# Modelos base
for m in "Categoria:categorias:nome,parent_id,descricao,ordem" "Equipamento:equipamentos:nome,marca,modelo,categoria_id,estado,quantidade,preco_aluguer_dia,preco_custo,armazem,notas" "NumeroSerie:numeros_serie:equipamento_id,numero_serie,qr_code,estado" "Orcamento:orcamentos:numero,cliente_nome,cliente_email,cliente_telefone,evento_nome,evento_local,data_inicio,data_fim,estado,valor_total,notas" "OrcamentoItem:orcamento_itens:orcamento_id,equipamento_id,quantidade,preco_unitario,dias,subtotal,subaluguer,fornecedor,custo_subaluguer" "GuiaTransporte:guia_transportes:numero,orcamento_id,tipo,estado,responsavel,observacoes" "GuiaItem:guia_itens:guia_transporte_id,equipamento_id,quantidade,notas" "Reparacao:reparacoes:equipamento_id,descricao_avaria,estado,tecnico,custo_reparacao,data_entrada,data_saida,notas_tecnicas" "Colaborador:colaboradores:nome,morada,bi_passaporte,funcao,competencias,idade,epis,dados_adicionais" "Entidade:entidades:nome,designacao_comercial,tipo_entidade,nif,pais,email,telefone,morada,notas,ativo" "Funcao:funcoes:nome,departamento_id,descricao,ativo" "EscalaTecnico:escalas_tecnicos:colaborador_id,orcamento_id,data_inicio,data_fim,hora_entrada,hora_saida,funcao,notas"; do
  IFS=':' read -r name table fields <<< "$m"
  fills="'${fields//,/\',\'}'"
  cat > "app/Models/${name}.php" << MODELEOF
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ${name} extends Model{protected \$table='${table}';protected \$fillable=[${fills}];}
MODELEOF
done

# Relações
cat > app/Models/Categoria.php << 'EOF'
<?php namespace App\Models;use Illuminate\Database\Eloquent\Model;class Categoria extends Model{protected $table='categorias';protected $fillable=['nome','parent_id','descricao','ordem'];public function parent(){return $this->belongsTo(Categoria::class,'parent_id');}public function children(){return $this->hasMany(Categoria::class,'parent_id');}public function equipamentos(){return $this->hasMany(Equipamento::class,'categoria_id');}}
EOF
cat > app/Models/Equipamento.php << 'EOF'
<?php namespace App\Models;use Illuminate\Database\Eloquent\Model;class Equipamento extends Model{protected $table='equipamentos';protected $fillable=['nome','marca','modelo','categoria_id','estado','quantidade','preco_aluguer_dia','preco_custo','armazem','notas'];public function categoria(){return $this->belongsTo(Categoria::class);}public function numerosSerie(){return $this->hasMany(NumeroSerie::class);}}
EOF
cat > app/Models/NumeroSerie.php << 'EOF'
<?php namespace App\Models;use Illuminate\Database\Eloquent\Model;class NumeroSerie extends Model{protected $table='numeros_serie';protected $fillable=['equipamento_id','numero_serie','qr_code','estado'];protected static function boot(){parent::boot();static::creating(function($m){if(!$m->qr_code)$m->qr_code='EQ-'.strtoupper(uniqid());});}public function equipamento(){return $this->belongsTo(Equipamento::class);}}
EOF
cat > app/Models/Orcamento.php << 'EOF'
<?php namespace App\Models;use Illuminate\Database\Eloquent\Model;class Orcamento extends Model{protected $table='orcamentos';protected $fillable=['numero','cliente_nome','cliente_email','cliente_telefone','evento_nome','evento_local','data_inicio','data_fim','estado','valor_total','notas'];public function itens(){return $this->hasMany(OrcamentoItem::class);}public function guias(){return $this->hasMany(GuiaTransporte::class);}public function escalas(){return $this->hasMany(EscalaTecnico::class);}}
EOF
cat > app/Models/Colaborador.php << 'EOF'
<?php namespace App\Models;use Illuminate\Database\Eloquent\Model;class Colaborador extends Model{protected $table='colaboradores';protected $fillable=['nome','morada','bi_passaporte','funcao','competencias','idade','epis','dados_adicionais'];public function equipamentos(){return $this->belongsToMany(Equipamento::class,'colaborador_equipamento')->withPivot('quantidade','data_atribuicao','data_devolucao','notas','numero_serie_id','tipo')->withTimestamps();}public function escalas(){return $this->hasMany(EscalaTecnico::class);}}
EOF
cat > app/Models/EscalaTecnico.php << 'EOF'
<?php namespace App\Models;use Illuminate\Database\Eloquent\Model;class EscalaTecnico extends Model{protected $table='escalas_tecnicos';protected $fillable=['colaborador_id','orcamento_id','data_inicio','data_fim','hora_entrada','hora_saida','funcao','notas'];public function colaborador(){return $this->belongsTo(Colaborador::class);}public function orcamento(){return $this->belongsTo(Orcamento::class);}}
EOF

# Observer
mkdir -p app/Observers
cat > app/Observers/OrcamentoObserver.php << 'EOF'
<?php namespace App\Observers;use App\Models\Orcamento;use App\Models\Equipamento;use Illuminate\Support\Facades\DB;
class OrcamentoObserver{public function updated(Orcamento $o){if($o->estado==='confirmado'&&$o->wasChanged('estado'))$this->baixa($o);if($o->wasChanged('estado')&&$o->getOriginal('estado')==='confirmado'&&$o->estado!=='confirmado')$this->devolve($o);}
private function baixa(Orcamento $o){foreach($o->itens as $i){if($i->subaluguer)continue;$e=Equipamento::find($i->equipamento_id);if(!$e)continue;$a=$e->quantidade;$e->quantidade=max(0,$e->quantidade-$i->quantidade);if($e->quantidade==0)$e->estado='alugado';$e->save();DB::table('logs_stock')->insert(['orcamento_id'=>$o->id,'equipamento_id'=>$e->id,'quantidade_antes'=>$a,'quantidade_depois'=>$e->quantidade,'acao'=>'baixa','created_at'=>now(),'updated_at'=>now()]);}}
private function devolve(Orcamento $o){foreach($o->itens as $i){if($i->subaluguer)continue;$e=Equipamento::find($i->equipamento_id);if(!$e)continue;$a=$e->quantidade;$e->quantidade+=$i->quantidade;if($e->quantidade>0&&$e->estado==='alugado')$e->estado='disponivel';$e->save();DB::table('logs_stock')->insert(['orcamento_id'=>$o->id,'equipamento_id'=>$e->id,'quantidade_antes'=>$a,'quantidade_depois'=>$e->quantidade,'acao'=>'devolucao','created_at'=>now(),'updated_at'=>now()]);}}}
EOF
cat > app/Providers/AppServiceProvider.php << 'EOF'
<?php namespace App\Providers;use Illuminate\Support\ServiceProvider;use App\Models\Orcamento;use App\Observers\OrcamentoObserver;class AppServiceProvider extends ServiceProvider{public function boot():void{Orcamento::observe(OrcamentoObserver::class);}}
EOF

# Admin
php artisan tinker --execute="App\Models\User::create(['name'=>'Administrador','email'=>'${ADMIN_EMAIL}','password'=>bcrypt('${ADMIN_PASS}'),'role'=>'admin','email_verified_at'=>now()]);" --quiet
echo "✅"
echo "[5/10] Resources + Dashboard + Widgets + Escala"

# Gerar Resources
for r in Categoria Equipamento Orcamento GuiaTransporte Reparacao Colaborador Entidade Funcao User; do
    php artisan make:filament-resource $r --generate --no-interaction --quiet
done

# Aplicar labels e grupos (igual à produção)
php artisan tinker --execute="
\$config = [
    'CategoriaResource' => ['label' => 'Equipamentos', 'plural' => 'Equipamentos', 'group' => 'Logística'],
    'EquipamentoResource' => ['label' => 'Equipamentos', 'plural' => 'Equipamentos', 'group' => 'Logística'],
    'OrcamentoResource' => ['label' => 'Orçamentos', 'plural' => 'Orçamentos', 'group' => 'Comercial'],
    'GuiaTransporteResource' => ['label' => 'Guias Transporte', 'plural' => 'Guias Transporte', 'group' => 'Logística'],
    'ReparacaoResource' => ['label' => 'Reparações', 'plural' => 'Reparações', 'group' => 'Logística'],
    'ColaboradorResource' => ['label' => 'Colaboradores', 'plural' => 'Colaboradores', 'group' => 'Logística'],
    'EntidadeResource' => ['label' => 'Entidades', 'plural' => 'Entidades', 'group' => 'Comercial'],
    'FuncaoResource' => ['label' => 'Funções', 'plural' => 'Funções', 'group' => 'Logística'],
    'UserResource' => ['label' => 'Users App', 'plural' => 'Users App', 'group' => 'App Admin'],
];
foreach(\$config as \$file => \$data) {
    \$path = 'app/Filament/Resources/'.\$file.'.php';
    if(!file_exists(\$path)) continue;
    \$content = file_get_contents(\$path);
    \$search = \"protected static ?string \$navigationIcon = 'heroicon-o-rectangle-stack';\";
    \$replace = \"protected static ?string \$navigationIcon = null;\n    protected static ?string \$navigationLabel = '{\$data['label']}';\n    protected static ?string \$pluralModelLabel = '{\$data['plural']}';\n    protected static ?string \$navigationGroup = '{\$data['group']}';\";
    \$content = str_replace(\$search, \$replace, \$content);
    file_put_contents(\$path, \$content);
}
echo 'OK';
" --quiet

# Dashboard + Widgets + Escala (páginas especiais)
mkdir -p app/Filament/Pages app/Filament/Widgets resources/views/filament/widgets resources/views/filament/pages resources/views/components resources/views/pdf

# Dashboard
cat > app/Filament/Pages/Dashboard.php << 'EOF'
<?php namespace App\Filament\Pages;use App\Filament\Widgets\CalendarioEventos;use App\Filament\Widgets\CalendarioMensal;use App\Filament\Widgets\StatsEquipamentos;use App\Filament\Widgets\AlertasConflitos;use Filament\Pages\Dashboard as BaseDashboard;class Dashboard extends BaseDashboard{protected static ?string $navigationIcon=null;protected static ?string $title='Dashboard';protected static ?string $navigationLabel='Dashboard';public function getWidgets():array{return[StatsEquipamentos::class,AlertasConflitos::class,CalendarioEventos::class,CalendarioMensal::class];}public function getColumns():int|string|array{return 1;}}
EOF

# Stats
cat > app/Filament/Widgets/StatsEquipamentos.php << 'EOF'
<?php namespace App\Filament\Widgets;use App\Models\Equipamento;use App\Models\Orcamento;use Filament\Widgets\StatsOverviewWidget as BaseWidget;use Filament\Widgets\StatsOverviewWidget\Stat;class StatsEquipamentos extends BaseWidget{protected function getStats():array{$d=Equipamento::where('estado','disponivel')->sum('quantidade');$t=Equipamento::sum('quantidade');$m=Equipamento::where('estado','manutencao')->sum('quantidade');$c=Orcamento::where('estado','confirmado')->count();$o=Orcamento::where('estado','orcamentacao')->count();$dr=Orcamento::where('estado','draft')->count();$ca=Orcamento::where('estado','cancelado')->count();return[Stat::make('Stock Disponível',$d)->description('Total: '.$t)->color('success'),Stat::make('Manutenção',$m)->description(Equipamento::where('estado','manutencao')->count().' equip.')->color($m>0?'danger':'success'),Stat::make('Orçamentos',$c+$o+$dr+$ca)->description(($c>0?'🟢'.$c.' ':'').($o>0?'🟡'.$o.' ':'').($dr>0?'🔵'.$dr.' ':'').($ca>0?'🔴'.$ca:''))->color('warning')];}}
EOF

# Alertas
cat > app/Filament/Widgets/AlertasConflitos.php << 'EOF'
<?php namespace App\Filament\Widgets;use App\Models\Orcamento;use Filament\Widgets\Widget;use Carbon\Carbon;class AlertasConflitos extends Widget{protected static string $view='filament.widgets.alertas-conflitos';protected int|string|array $columnSpan='full';public function getConflitos(){$c=[];$os=Orcamento::where('estado','confirmado')->where('data_fim','>=',Carbon::now())->with('itens.equipamento')->get();foreach($os as $o1){foreach($os as $o2){if($o1->id>=$o2->id)continue;$i1=Carbon::parse($o1->data_inicio);$f1=Carbon::parse($o1->data_fim);$i2=Carbon::parse($o2->data_inicio);$f2=Carbon::parse($o2->data_fim);if($i1->lte($f2)&&$f1->gte($i2)){$e1=$o1->itens->pluck('equipamento_id')->toArray();$e2=$o2->itens->pluck('equipamento_id')->toArray();$com=array_intersect($e1,$e2);if(!empty($com))$c[]=['o1'=>$o1->numero.' - '.$o1->cliente_nome,'o2'=>$o2->numero.' - '.$o2->cliente_nome,'p'=>$i1->format('d/m').'-'.$f1->format('d/m').' ↔ '.$i2->format('d/m').'-'.$f2->format('d/m'),'e'=>count($com)];}}}return $c;}}
EOF

# Calendário Semanal
cat > app/Filament/Widgets/CalendarioEventos.php << 'EOF'
<?php namespace App\Filament\Widgets;use App\Models\Orcamento;use Filament\Widgets\Widget;use Carbon\Carbon;class CalendarioEventos extends Widget{protected static string $view='filament.widgets.calendario-eventos';protected int|string|array $columnSpan='full';public function getOrcamentos(){$is=Carbon::now('Europe/Lisbon')->startOfWeek(Carbon::MONDAY)->startOfDay();$fs=Carbon::now('Europe/Lisbon')->endOfWeek(Carbon::SUNDAY)->endOfDay();return Orcamento::with(['itens.equipamento'])->whereIn('estado',['draft','confirmado','orcamentacao'])->where(function($q)use($is,$fs){$q->whereBetween('data_inicio',[$is,$fs])->orWhereBetween('data_fim',[$is,$fs])->orWhere(function($q2)use($is,$fs){$q2->where('data_inicio','<=',$is)->where('data_fim','>=',$fs);});})->orderBy('data_inicio')->get()->map(function($o)use($is){$in=Carbon::parse($o->data_inicio)->startOfDay();$fi=Carbon::parse($o->data_fim)->startOfDay();$ci=max(1,min(7,$is->diffInDays($in,false)+1));$cf=max(1,min(7,$is->diffInDays($fi,false)+1));$d=max(1,$cf-$ci+1);$cor=match($o->estado){'confirmado'=>'#10B981','orcamentacao'=>'#F59E0B','draft'=>'#3B82F6',default=>'#6B7280'};return(object)['id'=>$o->id,'numero'=>$o->numero,'cliente'=>$o->cliente_nome,'evento'=>$o->evento_nome,'local'=>$o->evento_local,'inicio'=>$o->data_inicio,'fim'=>$o->data_fim,'coluna_inicio'=>$ci,'duracao'=>$d,'cor'=>$cor,'estado'=>$o->estado,'total_equipamentos'=>$o->itens->sum('quantidade'),'valor_total'=>$o->valor_total];});}public function getDiaAtual():int{return Carbon::now('Europe/Lisbon')->dayOfWeekIso;}public function getSemanaAtual():array{$d=[];$i=Carbon::now('Europe/Lisbon')->startOfWeek(Carbon::MONDAY)->startOfDay();Carbon::setLocale('pt');for($x=0;$x<7;$x++){$dd=$i->copy()->addDays($x);$d[]=['nome'=>$dd->translatedFormat('D'),'dia'=>$dd->format('d'),'mes'=>$dd->format('M'),'data'=>$dd->format('Y-m-d'),'hoje'=>$dd->isToday()];}return $d;}}
EOF

# Calendário Mensal
cat > app/Filament/Widgets/CalendarioMensal.php << 'EOF'
<?php namespace App\Filament\Widgets;use App\Models\Orcamento;use Filament\Widgets\Widget;use Carbon\Carbon;class CalendarioMensal extends Widget{protected static string $view='filament.widgets.calendario-mensal';protected int|string|array $columnSpan='full';public $mesAtual;public $anoAtual;public function mount(){$this->mesAtual=Carbon::now()->month;$this->anoAtual=Carbon::now()->year;}public function mesAnterior(){if($this->mesAtual==1){$this->mesAtual=12;$this->anoAtual--;}else{$this->mesAtual--;}}public function mesSeguinte(){if($this->mesAtual==12){$this->mesAtual=1;$this->anoAtual++;}else{$this->mesAtual++;}}public function getDiasDoMes(){$im=Carbon::create($this->anoAtual,$this->mesAtual,1);$fm=$im->copy()->endOfMonth();$dias=[];$da=$im->copy()->startOfWeek(Carbon::MONDAY);for($i=0;$i<42;$i++){$dias[]=['data'=>$da->format('Y-m-d'),'dia'=>$da->day,'mes_atual'=>$da->month==$this->mesAtual,'hoje'=>$da->isToday(),'fim_semana'=>$da->isWeekend(),'eventos'=>[]];$da->addDay();}$evs=Orcamento::with('itens.equipamento')->whereIn('estado',['draft','orcamentacao','confirmado'])->where(function($q)use($im,$fm){$q->whereBetween('data_inicio',[$im,$fm])->orWhereBetween('data_fim',[$im,$fm])->orWhere(function($q2)use($im,$fm){$q2->where('data_inicio','<=',$im)->where('data_fim','>=',$fm);});})->orderBy('data_inicio')->get();foreach($dias as &$dia){foreach($evs as $ev){$ini=Carbon::parse($ev->data_inicio);$fim=Carbon::parse($ev->data_fim);if(Carbon::parse($dia['data'])->between($ini,$fim)){$cor=match($ev->estado){'confirmado'=>'#10B981','orcamentacao'=>'#F59E0B','draft'=>'#3B82F6',default=>'#6B7280'};$dia['eventos'][]=['id'=>$ev->id,'numero'=>$ev->numero,'cliente'=>$ev->cliente_nome,'evento'=>$ev->evento_nome,'local'=>$ev->evento_local,'estado'=>$ev->estado,'cor'=>$cor,'inicio'=>$ev->data_inicio,'fim'=>$ev->data_fim,'total_equipamentos'=>$ev->itens->sum('quantidade')];}}}return $dias;}public function getNomeMes():string{$meses=['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];return $meses[$this->mesAtual].' '.$this->anoAtual;}}
EOF

# Escala Técnicos
cat > app/Filament/Pages/EscalaTecnicos.php << 'EOF'
<?php
namespace App\Filament\Pages;
use Filament\Pages\Page;
use App\Models\Colaborador;
use App\Models\Orcamento;
use App\Models\EscalaTecnico;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class EscalaTecnicos extends Page
{
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationLabel = 'Escala de Técnicos';
    protected static ?string $title = 'Escala de Técnicos';
    protected static ?string $slug = 'escala-tecnicos';
    protected static string $view = 'filament.pages.escala-tecnicos';
    protected static ?string $navigationGroup = 'Logística';
    public $semanaInicio; public $tecnicos=[]; public $eventos=[]; public $escalasData=[];
    public $tecnicoSelecionado=null; public $mostrarModal=false;
    public function mount(){$this->semanaInicio=Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');$this->carregar();}
    public function semanaAnterior(){$this->semanaInicio=Carbon::parse($this->semanaInicio)->subWeek()->format('Y-m-d');$this->carregar();}
    public function semanaSeguinte(){$this->semanaInicio=Carbon::parse($this->semanaInicio)->addWeek()->format('Y-m-d');$this->carregar();}
    public function carregar(){$i=Carbon::parse($this->semanaInicio);$f=$i->copy()->addDays(6);$this->tecnicos=Colaborador::orderBy('nome')->get()->toArray();$this->eventos=Orcamento::whereIn('estado',['confirmado','orcamentacao','draft'])->where(function($q)use($i,$f){$q->whereBetween('data_inicio',[$i,$f])->orWhereBetween('data_fim',[$i,$f])->orWhere(function($q2)use($i,$f){$q2->where('data_inicio','<=',$i)->where('data_fim','>=',$f);});})->orderBy('data_inicio')->get()->toArray();$es=EscalaTecnico::with('colaborador','orcamento')->where(function($q)use($i,$f){$q->whereBetween('data_inicio',[$i,$f])->orWhereBetween('data_fim',[$i,$f]);})->get();$this->escalasData=[];foreach($es as $e){$cid=$e->colaborador_id;if(!isset($this->escalasData[$cid]))$this->escalasData[$cid]=[];$this->escalasData[$cid][]=['id'=>$e->id,'colaborador_id'=>$e->colaborador_id,'orcamento_id'=>$e->orcamento_id,'data_inicio'=>$e->data_inicio,'data_fim'=>$e->data_fim,'orcamento_numero'=>$e->orcamento->numero??'','orcamento_cliente'=>$e->orcamento->cliente_nome??''];}}
    public function abrirModal($tid){$this->tecnicoSelecionado=$tid;$this->mostrarModal=true;}
    public function fecharModal(){$this->mostrarModal=false;}
    public function alocarTecnico($cid,$oid){$c=Colaborador::find($cid);$o=Orcamento::find($oid);if(!$c||!$o)return;if(EscalaTecnico::where('colaborador_id',$cid)->where(function($q)use($o){$q->whereBetween('data_inicio',[$o->data_inicio,$o->data_fim])->orWhereBetween('data_fim',[$o->data_inicio,$o->data_fim]);})->exists()){Notification::make()->title('Conflito')->body($c->nome.' já está alocado.')->warning()->send();return;}EscalaTecnico::create(['colaborador_id'=>$cid,'orcamento_id'=>$oid,'data_inicio'=>$o->data_inicio,'data_fim'=>$o->data_fim,'funcao'=>$c->funcao]);$this->mostrarModal=false;Notification::make()->title('Alocado')->body($c->nome.' → '.$o->numero)->success()->send();$this->carregar();}
    public function removerAlocacao($eid){EscalaTecnico::find($eid)?->delete();Notification::make()->title('Removido')->success()->send();$this->carregar();}
    public function getDiasSemana(){$d=[];$i=Carbon::parse($this->semanaInicio);for($x=0;$x<7;$x++){$d[]=$i->copy()->addDays($x);}return $d;}
    public function temEscala($tid,$data){if(!isset($this->escalasData[$tid]))return null;foreach($this->escalasData[$tid] as $e){if(Carbon::parse($data)->between(Carbon::parse($e['data_inicio']),Carbon::parse($e['data_fim'])))return $e;}return null;}
}
EOF

echo "✅"
echo "[6/10] Views"

# Alertas
cat > resources/views/filament/widgets/alertas-conflitos.blade.php << 'EOF'
<div>@php $c=$this->getConflitos();@endphp@if(count($c)>0)<div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:16px;padding:20px 24px;margin-bottom:16px;"><div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;"><span style="font-size:20px;">⚠️</span><h3 style="margin:0;font-size:16px;font-weight:700;color:#991B1B;">Alertas de Conflito</h3><span style="background:#DC2626;color:white;padding:2px 10px;border-radius:10px;font-size:12px;">{{count($c)}}</span></div>@foreach($c as $x)<div style="background:white;padding:10px 14px;border-radius:10px;border:1px solid #FECACA;font-size:12px;margin-bottom:8px;"><div style="color:#991B1B;font-weight:600;">{{$x['o1']}} ↔ {{$x['o2']}}</div><div style="color:#64748B;margin-top:2px;">📅 {{$x['p']}} · 🔧 {{$x['e']}} equip.</div></div>@endforeach</div>@endif</div>
EOF

# Calendário Semanal
cat > resources/views/filament/widgets/calendario-eventos.blade.php << 'EOF'
<div style="background:#1E293B;border-radius:16px;padding:16px;border:1px solid #334155;overflow-x:auto;"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;"><div><h2 style="font-size:18px;font-weight:700;color:#F1F5F9;margin:0;">Agenda Semanal</h2><p style="font-size:12px;color:#94A3B8;">{{\Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY)->format('d M')}} → {{\Carbon\Carbon::now()->endOfWeek(\Carbon\Carbon::SUNDAY)->format('d M Y')}}</p></div></div><div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;margin-bottom:8px;">@foreach($this->getSemanaAtual() as $d)@php $bg=$d['hoje']?'#3B82F6':'#334155';@endphp<div style="padding:8px 4px;text-align:center;border-radius:8px;font-weight:600;font-size:11px;background:{{$bg}};color:{{$d['hoje']?'white':'#E2E8F0'}};"><div style="font-size:9px;">{{substr($d['nome'],0,3)}}</div><div style="font-size:16px;font-weight:700;">{{$d['dia']}}</div></div>@endforeach</div><div style="position:relative;min-height:100px;"><div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;">@for($i=0;$i<7;$i++)<div style="background:{{($this->getDiaAtual()==$i+1)?'#1E3A5F':'#0F172A'}};min-height:90px;border-radius:8px;border:1px solid #334155;"></div>@endfor</div><div style="position:absolute;top:4px;left:2px;right:2px;">@foreach($this->getOrcamentos() as $e)@php $l=$e->duracao*100/7;$esq=($e->coluna_inicio-1)*100/7;@endphp<div style="margin-bottom:3px;height:28px;"><div style="position:absolute;left:{{$esq}}%;width:{{$l}}%;min-width:40px;background:{{$e->cor}};color:white;padding:4px 6px;border-radius:6px;font-size:10px;font-weight:600;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">{{$e->numero}} | {{$e->cliente}}</div></div>@endforeach</div></div></div>
EOF

# Calendário Mensal
cat > resources/views/filament/widgets/calendario-mensal.blade.php << 'EOF'
<div style="background:#1E293B;border-radius:16px;padding:16px;border:1px solid #334155;"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;"><div style="display:flex;align-items:center;gap:8px;"><button wire:click="mesAnterior" style="background:#334155;color:#E2E8F0;border:none;padding:6px 12px;border-radius:8px;cursor:pointer;">←</button><h2 style="color:#F1F5F9;font-size:16px;font-weight:700;margin:0;">{{$this->getNomeMes()}}</h2><button wire:click="mesSeguinte" style="background:#334155;color:#E2E8F0;border:none;padding:6px 12px;border-radius:8px;cursor:pointer;">→</button></div></div><div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;margin-bottom:2px;">@foreach(['S','T','Q','Q','S','S','D'] as $dia)<div style="padding:4px;text-align:center;color:#94A3B8;font-size:9px;">{{$dia}}</div>@endforeach</div><div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;">@foreach($this->getDiasDoMes() as $dia)@php $bg='#0F172A';if($dia['hoje'])$bg='#1E3A5F';if(!$dia['mes_atual'])$bg='#0a0f1a';@endphp<div style="background:{{$bg}};border-radius:6px;padding:3px;min-height:50px;{{!$dia['mes_atual']?'opacity:0.4;':''}}"><div style="color:{{$dia['hoje']?'white':'#94A3B8'}};font-size:9px;margin-bottom:2px;">{{$dia['dia']}}</div>@foreach($dia['eventos'] as $ev)<div style="background:{{$ev['cor']}};color:white;padding:1px 3px;border-radius:3px;font-size:7px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;margin-bottom:1px;">{{$ev['cliente']}}</div>@endforeach</div>@endforeach</div></div>
EOF

# Escala Técnicos
cat > resources/views/filament/pages/escala-tecnicos.blade.php << 'EOF'
<x-filament-panels::page>
<div style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;">
<a href="{{ route('filament.admin.resources.categorias.index') }}" style="display:inline-flex;align-items:center;gap:6px;color:#94A3B8;text-decoration:none;font-size:13px;padding:8px 16px;border:1px solid #334155;border-radius:8px;">← Voltar</a>
<div style="display:flex;align-items:center;gap:12px;"><button wire:click="semanaAnterior" style="background:#334155;color:#E2E8F0;border:none;padding:8px 16px;border-radius:8px;cursor:pointer;">←</button><span style="color:#F1F5F9;font-weight:600;">{{ \Carbon\Carbon::parse($semanaInicio)->format('d M') }} - {{ \Carbon\Carbon::parse($semanaInicio)->addDays(6)->format('d M Y') }}</span><button wire:click="semanaSeguinte" style="background:#334155;color:#E2E8F0;border:none;padding:8px 16px;border-radius:8px;cursor:pointer;">→</button></div></div>
@if($mostrarModal)<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:9999;display:flex;justify-content:center;align-items:center;" wire:click="fecharModal"><div wire:click.stop style="background:#1E293B;border-radius:16px;padding:20px;max-width:500px;width:100%;max-height:70vh;overflow-y:auto;border:1px solid #334155;"><h3 style="color:#F1F5F9;margin-bottom:16px;">Selecionar Evento</h3>@foreach($eventos as $ev)@php $cor=match($ev['estado']){'confirmado'=>'#10B981','orcamentacao'=>'#F59E0B','draft'=>'#3B82F6',default=>'#6B7280'};@endphp<div wire:click="alocarTecnico({{$tecnicoSelecionado}},{{$ev['id']}})" style="padding:12px;background:#0F172A;border:1px solid #334155;border-radius:8px;cursor:pointer;margin-bottom:8px;"><div style="display:flex;justify-content:space-between;"><span style="color:{{$cor}};font-weight:600;">{{$ev['numero']}}</span><span style="color:#94A3B8;font-size:11px;">{{$ev['data_inicio']}}→{{$ev['data_fim']}}</span></div><div style="color:#E2E8F0;margin-top:4px;">{{$ev['cliente_nome']}}</div></div>@endforeach<button wire:click="fecharModal" style="margin-top:12px;padding:8px 20px;background:#334155;color:white;border:none;border-radius:8px;cursor:pointer;">Fechar</button></div></div>@endif
<div style="background:#1E293B;border-radius:16px;padding:16px;border:1px solid #334155;overflow-x:auto;"><div style="min-width:900px;"><div style="display:grid;grid-template-columns:180px repeat(7,1fr);gap:4px;margin-bottom:8px;"><div style="color:#94A3B8;font-size:11px;font-weight:600;padding:4px;">Eventos</div>@foreach($this->getDiasSemana() as $dia)<div style="background:{{$dia->isToday()?'#1E3A5F':'#0F172A'}};border:1px solid {{$dia->isToday()?'#3B82F6':'#334155'}};border-radius:8px;padding:6px 4px;text-align:center;"><div style="color:{{$dia->isToday()?'white':'#94A3B8'}};font-size:10px;">{{$dia->format('D')}}</div><div style="color:{{$dia->isToday()?'white':'#E2E8F0'}};font-size:13px;font-weight:700;">{{$dia->format('d')}}</div></div>@endforeach</div><div style="display:grid;grid-template-columns:180px repeat(7,1fr);gap:4px;margin-bottom:16px;"><div></div>@foreach($this->getDiasSemana() as $dia)<div style="min-height:44px;background:#0F172A;border-radius:6px;border:1px solid #1E293B;padding:2px;">@foreach($eventos as $ev)@php $ini=\Carbon\Carbon::parse($ev['data_inicio']);$fim=\Carbon\Carbon::parse($ev['data_fim']);@endphp@if($dia->between($ini,$fim))@php $cor=match($ev['estado']){'confirmado'=>'#10B981','orcamentacao'=>'#F59E0B','draft'=>'#3B82F6',default=>'#6B7280'};@endphp<div style="background:{{$cor}};color:white;padding:3px 6px;border-radius:4px;font-size:9px;font-weight:600;">{{$ev['numero']}}|{{\Illuminate\Support\Str::limit($ev['cliente_nome'],12)}}</div>@endif@endforeach</div>@endforeach</div>@foreach($tecnicos as $t)@php $f=$t['funcao']??'';$cores=['Audio'=>'34,197,94','Video'=>'59,130,246','Estruturas'=>'245,158,11','Iluminacao'=>'239,68,68','Mobiliario'=>'139,92,246'];$rgb=$cores[$f]??'107,114,128';@endphp<div style="display:grid;grid-template-columns:180px repeat(7,1fr);gap:4px;margin-bottom:4px;align-items:center;"><div style="display:flex;align-items:center;gap:8px;padding:6px 10px;background:#0F172A;border-radius:6px;border:1px solid #1E293B;"><span style="width:10px;height:10px;border-radius:50%;background:rgb({{$rgb}});"></span><span style="color:#E2E8F0;font-size:12px;font-weight:500;">{{$t['nome']}}</span></div>@foreach($this->getDiasSemana() as $dia)@php $esc=$this->temEscala($t['id'],$dia->format('Y-m-d'));@endphp<div style="min-height:38px;background:{{$esc?'rgba('.$rgb.',0.6)':'#0F172A'}};border:2px solid {{$esc?'rgb('.$rgb.')':'#1E293B'}};border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;" @if(!$esc) wire:click="abrirModal({{$t['id']}})" @else wire:click="removerAlocacao({{$esc['id']}})" @endif>@if($esc)<span style="font-size:9px;color:rgb({{$rgb}});font-weight:700;padding:2px 6px;background:rgba({{$rgb}},0.2);border-radius:4px;">{{$esc['orcamento_numero']}}|{{\Illuminate\Support\Str::limit($esc['orcamento_cliente'],10)}}</span>@else<span style="font-size:16px;color:#334155;">+</span>@endif</div>@endforeach</div>@endforeach</div></div>
</x-filament-panels::page>
EOF

# Footer
cat > resources/views/components/footer.blade.php << 'EOF'
<div style="position:fixed;bottom:0;left:0;right:0;background:#0F172A;border-top:1px solid #1E293B;padding:8px 24px;display:flex;justify-content:space-between;align-items:center;z-index:50;font-size:11px;"><span style="color:#64748B;">Smartchoice©2026 - All rights reserved</span><span style="color:#475569;font-size:10px;">App SmartManager - Version 1.0.72026 by Nelson Teixeira</span></div><style>.fi-main{padding-bottom:40px!important;}</style>
EOF

# PDF Orçamento
cat > resources/views/pdf/orcamento.blade.php << 'EOF'
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Orçamento {{$orcamento->numero}}</title><style>body{font-family:sans-serif;font-size:10px;color:#1e293b;margin:0;padding:0;}.header{background:#1E293B;padding:20px 28px;display:flex;justify-content:space-between;}.logo{font-size:22px;font-weight:700;color:white;}.logo span{color:#3B82F6;}.info{text-align:right;color:white;}.info h1{font-size:18px;margin:0;}.content{padding:20px 28px;}table{width:100%;border-collapse:collapse;}th{text-align:left;padding:6px 8px;font-size:8px;color:#94a3b8;border-bottom:1px solid #e2e8f0;}td{padding:6px 8px;border-bottom:1px solid #f8fafc;font-size:10px;}.total{text-align:right;font-size:14px;font-weight:700;margin-top:20px;padding:12px 16px;border:1px solid #3B82F6;border-radius:8px;color:#3B82F6;}.footer{margin-top:24px;font-size:8px;color:#cbd5e1;text-align:center;border-top:1px solid #f1f5f9;padding:12px 0 0;}</style></head><body><div class="header"><div class="logo"><span>Smart</span>choice</div><div class="info"><h1>Orçamento</h1><div>{{$orcamento->numero}}</div><div>{{ucfirst($orcamento->estado)}}</div></div></div><div class="content"><p><strong>Cliente:</strong> {{$orcamento->cliente_nome}}</p><p><strong>Evento:</strong> {{$orcamento->evento_nome?:'—'}} | <strong>Local:</strong> {{$orcamento->evento_local?:'—'}}</p><p><strong>Período:</strong> {{\Carbon\Carbon::parse($orcamento->data_inicio)->format('d/m/Y')}} → {{\Carbon\Carbon::parse($orcamento->data_fim)->format('d/m/Y')}}</p><table><tr><th>Equipamento</th><th>Qtd</th><th>Preço/Dia</th><th>Dias</th><th>Subtotal</th></tr>@foreach($orcamento->itens as $i)<tr><td>{{$i->equipamento->nome??'N/A'}}</td><td>{{$i->quantidade}}</td><td>{{number_format($i->preco_unitario,2)}}€</td><td>{{$i->dias}}</td><td>{{number_format($i->subtotal,2)}}€</td></tr>@endforeach</table><div class="total">Total {{number_format($orcamento->valor_total,2)}}€</div></div><div class="footer">Smartchoice Event Manager · {{now()->format('d/m/Y H:i')}}</div></body></html>
EOF

# PDF Guia
cat > resources/views/pdf/guia.blade.php << 'EOF'
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Guia {{$guia->numero}}</title><style>body{font-family:sans-serif;font-size:10px;margin:0;padding:20px;}h1{font-size:16px;color:#1E293B;}table{width:100%;border-collapse:collapse;}th,td{padding:6px;border-bottom:1px solid #e2e8f0;text-align:left;}</style></head><body><h1>Guia de Transporte: {{$guia->numero}}</h1><p>Tipo: {{$guia->tipo}} | Estado: {{$guia->estado}}</p><p>Responsável: {{$guia->responsavel?:'—'}}</p><table><tr><th>Equipamento</th><th>Qtd</th></tr>@foreach($guia->itens as $i)<tr><td>{{$i->equipamento->nome??'N/A'}}</td><td>{{$i->quantidade}}</td></tr>@endforeach</table></body></html>
EOF

# PDF Reparação
cat > resources/views/pdf/reparacao.blade.php << 'EOF'
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Reparação #{{$reparacao->id}}</title><style>body{font-family:sans-serif;font-size:10px;margin:0;padding:20px;}h1{font-size:16px;color:#1E293B;}table{width:100%;border-collapse:collapse;}th,td{padding:6px;border-bottom:1px solid #e2e8f0;text-align:left;}</style></head><body><h1>Reparação #{{$reparacao->id}}</h1><p>Equipamento: {{$reparacao->equipamento->nome??'N/A'}}</p><p>Estado: {{$reparacao->estado}} | Técnico: {{$reparacao->tecnico?:'—'}}</p><p>Avaria: {{$reparacao->descricao_avaria}}</p><p>Custo: {{number_format($reparacao->custo_reparacao,2)}}€</p></body></html>
EOF

# PDF Etiquetas
cat > resources/views/pdf/etiquetas.blade.php << 'EOF'
<!DOCTYPE html><html><head><meta charset="utf-8"><style>@page{size:A4;margin:2mm;}body{font-family:Arial;margin:0;}table{width:100%;border-collapse:collapse;}td{width:50mm;height:25mm;border:0.5px dashed #e2e8f0;padding:1mm 2mm;vertical-align:middle;}.inner{display:flex;align-items:center;gap:2mm;}.qr{width:15mm;height:15mm;}.nome{font-size:6px;font-weight:700;text-transform:uppercase;}.sn{font-size:6px;color:#ef4444;font-weight:700;}</style></head><body><table>@php $c=0;@endphp@foreach($equipamento->numerosSerie as $ns)@if($c%3==0)<tr>@endif<td><div class="inner"><img src="data:image/svg+xml;base64,{{base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::size(55)->generate($ns->qr_code))}}" class="qr"><div><div class="nome">{{\Illuminate\Support\Str::limit($equipamento->nome,16)}}</div><div class="sn">S/N: {{$ns->numero_serie}}</div></div></div></td>@php$c++;@endphp@if($c%3==0)</tr>@endif@endforeach@if($c%3!=0)</tr>@endif</table></body></html>
EOF

# PDF Etiqueta Individual
cat > resources/views/pdf/etiqueta-individual.blade.php << 'EOF'
<!DOCTYPE html><html><head><meta charset="utf-8"><style>@page{size:50mm 25mm;margin:1.5mm;}body{font-family:Arial;margin:0;display:flex;align-items:center;justify-content:center;height:100%;}.et{display:flex;align-items:center;gap:3mm;}.qr{width:18mm;height:18mm;}.nome{font-size:7px;font-weight:700;text-transform:uppercase;}.sn{font-size:7px;color:#ef4444;font-weight:700;}</style></head><body><div class="et"><img src="data:image/svg+xml;base64,{{base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::size(70)->generate($ns->qr_code))}}" class="qr"><div><div class="nome">{{\Illuminate\Support\Str::limit($ns->equipamento->nome,20)}}</div><div class="sn">S/N: {{$ns->numero_serie}}</div></div></div></body></html>
EOF

echo "✅"
echo "[7/10] Provider + Nginx + Rotas"

# Provider
cat > app/Providers/Filament/AdminPanelProvider.php << 'EOF'
<?php
namespace App\Providers\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel->default()->id('admin')->path('admin')->login()
            ->brandName('Smartchoice Event Manager')->brandLogo(asset('images/logo.png'))->favicon(asset('images/favicon.ico'))
            ->colors(['primary'=>Color::Blue,'gray'=>Color::Slate])->font('Inter')->maxContentWidth('full')->topNavigation()
            ->navigationItems([
                NavigationItem::make('Equipamentos')->url(fn()=>route('filament.admin.resources.categorias.index'))->group('Comercial')->sort(2),
                NavigationItem::make('LED Calculator')->url('https://led.smartvideo.tech')->icon('heroicon-o-calculator')->group('Comercial')->openUrlInNewTab()->sort(3),
            ])
            ->discoverResources(in:app_path('Filament/Resources'),for:'App\\Filament\\Resources')
            ->discoverPages(in:app_path('Filament/Pages'),for:'App\\Filament\\Pages')
            ->pages([\App\Filament\Pages\Dashboard::class])
            ->discoverWidgets(in:app_path('Filament/Widgets'),for:'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([EncryptCookies::class,AddQueuedCookiesToResponse::class,StartSession::class,AuthenticateSession::class,ShareErrorsFromSession::class,VerifyCsrfToken::class,SubstituteBindings::class,DisableBladeIconComponents::class,DispatchServingFilamentEvent::class])
            ->authMiddleware([Authenticate::class])
            ->renderHook('panels::body.end',fn()=>view('components.footer'));
    }
}
EOF

# Nginx
cat > /etc/nginx/sites-available/gestao-eventos << NGXEOF
server {
    listen 80;
    server_name _;
    root /var/www/gestao-eventos/public;
    index index.php index.html;
    charset utf-8;
    client_max_body_size 100M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location /led {
        alias /var/www/gestao-eventos/public/led;
        try_files \$uri \$uri/ /led/index.html;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:${PHP_SOCK};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
NGXEOF
ln -sf /etc/nginx/sites-available/gestao-eventos /etc/nginx/sites-enabled/ 2>/dev/null
rm -f /etc/nginx/sites-enabled/default 2>/dev/null
nginx -t && systemctl restart nginx

# Rotas
cat > routes/web.php << 'EOF'
<?php
use Illuminate\Support\Facades\Route;
use App\Models\Equipamento;
use App\Models\Orcamento;
use App\Models\GuiaTransporte;
use App\Models\Reparacao;
use App\Models\Categoria;
use App\Models\NumeroSerie;
use Barryvdh\DomPDF\Facade\Pdf;

Route::get('/', fn()=>redirect('/admin'));
Route::get('/login', fn()=>redirect('/admin/login'));

Route::get('/orcamento/{orcamento}/pdf', fn(Orcamento $o)=>Pdf::loadView('pdf.orcamento',['orcamento'=>$o])->download('orcamento-'.$o->numero.'.pdf'))->name('orcamento.pdf');
Route::get('/guia/{guia}/pdf', fn(GuiaTransporte $g)=>Pdf::loadView('pdf.guia',['guia'=>$g])->download('guia-'.$g->numero.'.pdf'))->name('guia.pdf');
Route::get('/reparacao/{reparacao}/pdf', fn(Reparacao $r)=>Pdf::loadView('pdf.reparacao',['reparacao'=>$r])->download('reparacao-'.$r->id.'.pdf'))->name('reparacao.pdf');
Route::get('/etiquetas/{equipamento}', fn(Equipamento $e)=>Pdf::loadView('pdf.etiquetas',['equipamento'=>$e])->download('etiquetas-'.\Illuminate\Support\Str::slug($e->nome).'.pdf'))->name('etiquetas.pdf');
Route::get('/etiqueta/{numeroserie}', fn(NumeroSerie $n)=>Pdf::loadView('pdf.etiqueta-individual',['ns'=>$n])->download('etiqueta-'.$n->numero_serie.'.pdf'))->name('etiqueta.individual');

Route::get('/export/categorias', function(){
    $cats=Categoria::with('parent')->get();
    $csv="ID;Nome;Parent ID;Categoria Pai;Tipo\n";
    foreach($cats as $c){$t=!$c->parent_id?'Departamento':($c->parent&&!$c->parent->parent_id?'Familia':'SubFamilia');$csv.=$c->id.';'.$c->nome.';'.($c->parent_id??'').';'.($c->parent->nome??'').';'.$t."\n";}
    return response($csv)->header('Content-Type','text/csv; charset=utf-8')->header('Content-Disposition','attachment; filename=categorias.csv');
})->name('export.categorias');

Route::get('/export/equipamentos', function(){
    $eqs=Equipamento::with(['categoria.parent.parent','numerosSerie'])->orderBy('nome')->get();
    $csv="Nome;Departamento;Familia;SubFamilia;Armazem;Quantidade;Series;PrecoDia\n";
    foreach($eqs as $eq){$cat=$eq->categoria;$d='';$f='';$s='';
        if($cat){if($cat->parent&&$cat->parent->parent){$d=$cat->parent->parent->nome;$f=$cat->parent->nome;$s=$cat->nome;}elseif($cat->parent){$d=$cat->parent->nome;$f=$cat->nome;}else{$d=$cat->nome;}}
        $az=$eq->armazem?:'';$sr=$eq->numerosSerie->pluck('numero_serie')->filter()->implode(' | ');$pr=$eq->preco_aluguer_dia?number_format($eq->preco_aluguer_dia,2,'.',''):'0.00';
        $csv.=str_replace(';',',',$eq->nome).';'.$d.';'.$f.';'.$s.';'.$az.';'.$eq->quantidade.';'.$sr.';'.$pr."\n";}
    return response("\xEF\xBB\xBF".$csv)->header('Content-Type','text/csv; charset=utf-8')->header('Content-Disposition','attachment; filename=equipamentos.csv');
})->name('export.equipamentos');
EOF

echo "✅"

echo "[8/10] Assets"
mkdir -p public/images public/led
cat > public/images/logo1.svg << 'SVGEOF'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 636 637"><defs><style>.c1,.c3{fill:none;stroke:#fff;stroke-miterlimit:10;stroke-width:6}.c2{fill:#fff}</style></defs><path class="c1" d="M369 395h-243c-11 0-18-10-14-22L187 133a34 34 0 0 1 19-20v0a443 443 0 0 0-17 46c-35 111-12 166 69 166h96q38 0 22 53a134 134 0 0 1-7 17"/><path class="c1" d="M473 276q-8-65-84-65H294q-38 0-22-52Q284 122 306 111l198-1c12 0 18 10 14 23Z"/><path class="c2" d="M103 458q5 0 5 5t-5 5H81v10h22q9 0 14-5t5-10-5-11-14-5h-11q-3 0-4-2t-1-3 1-3 5-1h21v-10H92q-9 0-14 5t-5 11 5 10 14 5Z"/><path class="c2" d="M149 478h10v-36h8q4 0 4 4v32h10v-32q0-9-5-14t-14-5h-22v46h9v-36h10Z"/><path class="c2" d="M219 442q5 0 5 4v18q0 5-5 5h-14q-5 0-5-5v0q0-3 1-4t4-1h16v-10h-17q-9 0-14 5t-5 14v0q0 9 5 14t14 5h15q9 0 14-5t5-14v-18q0-9-5-14t-14-5h-25v10Z"/><path class="c2" d="M253 446q0-4 5-4h6v-10h-6q-9 0-14 5t-5 14v32h10Z"/><polygon class="c2" points="273 442 277 442 277 478 286 478 286 442 294 442 294 432 286 432 286 414 277 414 277 432 273 432"/><path class="c2" d="M319 471q-4 0-6-2t-2-6v-19q0-4 2-6t6-2h21v-7h-21q-9 0-13 4t-4 13v19q0 9 4 13t13 4h21v-7Z"/><path class="c2" d="M392 446q0-9-4-13t-13-4h-22v-16h-7v65h7v-39h22q4 0 6 2t2 6v31h7Z"/><path class="c2" d="M403 465q0 9 5 13t13 4h16q9 0 13-4t4-13v-19q0-9-4-13t-13-4h-16q-9 0-13 4t-4 13Zm13 6q-4 0-6-2t-2-6v-19q0-4 2-6t6-2h16q4 0 6 2t2 6v19q0 4-2 6t-6 2Z"/><path class="c2" d="M458 478h7v-46h-7Zm-1-56h9v-9h-9Z"/><path class="c2" d="M487 471q-4 0-6-2t-2-6v-19q0-4 2-6t6-2h21v-7h-21q-9 0-13 4t-4 13v19q0 9 4 13t13 4h21v-7Z"/><path class="c2" d="M531 471q-4 0-6-2t-2-6v-19q0-4 2-6t6-2h16q4 0 6 2t2 6v1q0 4-2 6t-6 2h-18v7h17q9 0 13-4t4-13v-1q0-9-4-13t-13-4h-16q-9 0-13 4t-4 13v19q0 9 4 13t13 4h25v-7Z"/><circle class="c3" cx="320" cy="318" r="300"/></svg>
SVGEOF

echo "✅"

echo "[9/10] Permissões"
chown -R www-data:www-data /var/www/gestao-eventos
chmod -R 775 /var/www/gestao-eventos/storage /var/www/gestao-eventos/bootstrap/cache
echo "✅"

echo "[10/10] Finalizar"
php artisan optimize:clear
ufw allow 22/tcp 2>/dev/null; ufw allow 80/tcp 2>/dev/null; ufw --force enable 2>/dev/null
sleep 2
S=$(curl -s -o /dev/null -w "%{http_code}" -L http://localhost/admin/login 2>/dev/null || echo "000")

echo ""
echo "╔══════════════════════════════════╗"
echo "║  ✅ SMARTCHOICE EVENT MANAGER   ║"
echo "║  v1.7 - Instalação Completa     ║"
echo "╚══════════════════════════════════╝"
echo "  🌐 http://${IP}/admin/login"
echo "  👤 ${ADMIN_EMAIL} / ${ADMIN_PASS}"
echo "  📊 HTTP: ${S}"
echo ""
echo "  📋 ESTRUTURA (baseada na Produção):"
echo "  🏠 Dashboard (Stats + Alertas + Agenda + Calendário Mensal)"
echo "  📦 Logística: Equipamentos | Guias Transporte | Reparações | Colaboradores | Funções | Escala Técnicos"
echo "  💰 Comercial: Orçamentos | Entidades | LED Calculator"
echo "  👤 App Admin: Users App"
echo "  📄 PDFs: Orçamento | Guia | Reparação | Etiquetas QR"
echo "  📥 Export: Equipamentos CSV | Categorias CSV"
