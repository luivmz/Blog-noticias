#!/bin/zsh
set -eu
cd "${0:A:h}/.."
pa02_evidence="../evidencias"
mkdir -p "$pa02_evidence"
php artisan migrate:status > "$pa02_evidence/migrations.txt"
pa02_temp=$(mktemp -d)
trap 'rm -rf "$pa02_temp"' EXIT
pa02_url='http://127.0.0.1:8020'
: > "$pa02_evidence/smoke-http.txt"
for pa02_path in / /noticias /login /categorias /noticias/no-existe /noticias/una-guia-breve-para-organizar-archivos-del-proyecto /admin; do
  pa02_code=$(curl -sS --max-time 10 -o /dev/null -w '%{http_code}' "$pa02_url$pa02_path")
  print -r -- "GET $pa02_path $pa02_code" >> "$pa02_evidence/smoke-http.txt"
done
curl -sS --max-time 10 -c "$pa02_temp/cookies" "$pa02_url/login" -o "$pa02_temp/login"
pa02_token=$(python3 - "$pa02_temp/login" <<'PY'
import re,sys
print(re.search(r'name="_token" value="([^"]+)"',open(sys.argv[1]).read()).group(1))
PY
)
pa02_code=$(curl -sS --max-time 10 -b "$pa02_temp/cookies" -c "$pa02_temp/cookies" -o /dev/null -w '%{http_code}' --data-urlencode "_token=$pa02_token" --data-urlencode 'email=admin@example.test' --data-urlencode 'password=Admin123!' "$pa02_url/login")
print -r -- "POST /login válido $pa02_code" >> "$pa02_evidence/smoke-http.txt"
for pa02_path in /admin /admin/noticias /admin/noticias/create /admin/noticias/18/edit; do
  pa02_code=$(curl -sS --max-time 10 -b "$pa02_temp/cookies" -o /dev/null -w '%{http_code}' "$pa02_url$pa02_path")
  print -r -- "GET autenticado $pa02_path $pa02_code" >> "$pa02_evidence/smoke-http.txt"
done
curl -sS --max-time 10 -b "$pa02_temp/cookies" "$pa02_url/admin" -o "$pa02_temp/admin"
pa02_token=$(python3 - "$pa02_temp/admin" <<'PY'
import re,sys
print(re.search(r'name="_token" value="([^"]+)"',open(sys.argv[1]).read()).group(1))
PY
)
pa02_code=$(curl -sS --max-time 10 -b "$pa02_temp/cookies" -c "$pa02_temp/cookies" -o /dev/null -w '%{http_code}' --data-urlencode "_token=$pa02_token" "$pa02_url/logout")
print -r -- "POST /logout $pa02_code" >> "$pa02_evidence/smoke-http.txt"
pa02_code=$(curl -sS --max-time 10 -b "$pa02_temp/cookies" -o /dev/null -w '%{http_code}' "$pa02_url/admin")
print -r -- "GET /admin después logout $pa02_code" >> "$pa02_evidence/smoke-http.txt"
pa02_code=$(curl -sS --max-time 10 -o /dev/null -w '%{http_code}' -X POST "$pa02_url/login")
print -r -- "POST /login sin CSRF $pa02_code" >> "$pa02_evidence/smoke-http.txt"
: > "$pa02_evidence/performance.txt"
print 'Mediciones curl locales, sin representar producción; cinco muestras consecutivas por URL.' >> "$pa02_evidence/performance.txt"
for pa02_path in / /noticias /login; do
  for pa02_sample in 1 2 3 4 5; do
    curl -sS --max-time 10 -o /dev/null -w "GET $pa02_path muestra=$pa02_sample http_code=%{http_code} time_total=%{time_total}\n" "$pa02_url$pa02_path" >> "$pa02_evidence/performance.txt"
  done
done
cat "$pa02_evidence/smoke-http.txt"
cat "$pa02_evidence/performance.txt"
