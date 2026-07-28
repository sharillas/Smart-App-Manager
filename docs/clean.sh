#!/bin/bash
# =============================================
# LIMPEZA COMPLETA DA VPS PARA REINSTALAÇÃO
# =============================================
set -e
clear
echo "🧹 A limpar VPS para reinstalação limpa..."

# 1. Parar serviços
systemctl stop nginx 2>/dev/null
systemctl stop php*-fpm 2>/dev/null

# 2. Remover projeto Laravel
rm -rf /var/www/gestao-eventos

# 3. Remover configurações Nginx
rm -f /etc/nginx/sites-available/gestao-eventos
rm -f /etc/nginx/sites-enabled/gestao-eventos
rm -f /etc/nginx/sites-enabled/led-calculator
rm -f /etc/nginx/sites-enabled/default
rm -f /etc/nginx/sites-available/default

# 4. Remover credenciais
rm -f /root/credentials.txt
rm -f /root/install.sh

# 5. Limpar cache do Composer
rm -rf /root/.composer/cache 2>/dev/null

# 6. Remover base de dados SQLite extra
rm -f /var/www/gestao-eventos/database/database.sqlite 2>/dev/null

# 7. Remover logs
rm -f /var/log/nginx/error.log 2>/dev/null
rm -f /var/log/php*-fpm.log 2>/dev/null

# 8. Reiniciar Nginx (vai falhar se não houver sites, mas não faz mal)
nginx -t 2>/dev/null && systemctl restart nginx 2>/dev/null || true

echo ""
echo "✅ VPS LIMPA!"
echo "🚀 Pronta para reinstalar:"
echo "   wget -O install.sh https://raw.githubusercontent.com/sharillas/Smart-App-Manager/main/docs/install.sh && bash install.sh"
