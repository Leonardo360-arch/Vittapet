# Recuperacao de acesso administrativo local (Windows PowerShell 5.1 ou 7).
# Nao reinicializa volumes, nao altera migrations e nao mostra a senha no terminal.
[CmdletBinding()]
param(
    [string]$AdminEmail = '',
    [string]$AdminName = 'Administrador'
)

$ErrorActionPreference = 'Stop'
Set-Location (Resolve-Path (Join-Path $PSScriptRoot '..')).Path

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    throw 'Docker nao encontrado no PATH. Abra o Docker Desktop e reinicie o terminal.'
}
if (-not (Test-Path './compose.local.yaml')) {
    throw 'Arquivo compose.local.yaml nao encontrado na raiz do projeto.'
}
if (Test-Path './config/local.php') {
    throw 'config/local.php sobrescreve as variaveis de banco do Docker. Verifique a configuracao antes de continuar.'
}

# Evita tratar a saida de diagnostico do Docker como erro do PowerShell 5.1.
docker compose -f compose.local.yaml ps
if ($LASTEXITCODE -ne 0) {
    throw 'Falha ao consultar containers. Verifique se Docker Desktop esta iniciado.'
}

$running = docker compose -f compose.local.yaml ps --status running --services
if ($LASTEXITCODE -ne 0 -or $running -notcontains 'app' -or $running -notcontains 'db') {
    throw 'Containers app e db precisam estar em execucao. Rode: docker compose -f compose.local.yaml up -d'
}

if ([string]::IsNullOrWhiteSpace($AdminEmail)) {
    $AdminEmail = Read-Host 'E-mail do administrador (existente ou novo)'
}
$AdminEmail = $AdminEmail.Trim()
if ($AdminEmail -notmatch '^[^\s@]+@[^\s@]+\.[^\s@]+$') {
    throw 'Informe um endereco de e-mail valido.'
}

$secure = Read-Host 'Nova senha de administrador (minimo 12 caracteres)' -AsSecureString
$pointer = [IntPtr]::Zero
try {
    $pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
    $plain = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer)
    if ($plain.Length -lt 12) { throw 'A senha precisa ter no minimo 12 caracteres.' }

    $env:ADMIN_EMAIL = $AdminEmail
    $env:ADMIN_NAME = $AdminName
    $env:ADMIN_PASSWORD = $plain

    docker compose -f compose.local.yaml exec -T -e ADMIN_EMAIL -e ADMIN_NAME -e ADMIN_PASSWORD app php scripts/create_admin.php
    if ($LASTEXITCODE -ne 0) {
        throw 'Nao foi possivel criar/atualizar o administrador. Rode scripts/check_local.php e consulte logs app/db.'
    }
    Write-Host 'Acesso local atualizado com sucesso.'
    Write-Host 'Login: http://localhost:8080/login.php'
    Write-Host ('E-mail: ' + $AdminEmail)
}
finally {
    if ($pointer -ne [IntPtr]::Zero) { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer) }
    Remove-Item Env:ADMIN_PASSWORD,Env:ADMIN_EMAIL,Env:ADMIN_NAME -ErrorAction SilentlyContinue
    Remove-Variable plain,secure -ErrorAction SilentlyContinue
}
