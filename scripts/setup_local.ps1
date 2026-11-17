# Preparação interativa para Windows PowerShell 5.1+ / PowerShell 7.
# NÃO grava a senha em arquivos nem no Git. Não remove volumes do Docker.
[CmdletBinding()]
param(
    [string]$AdminEmail = '',
    [string]$AdminName = 'Administrador'
)
$ErrorActionPreference = 'Stop'
Set-Location (Resolve-Path (Join-Path $PSScriptRoot '..')).Path

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    throw 'Docker CLI não encontrado. Instale e abra o Docker Desktop.'
}
docker info *> $null
if ($LASTEXITCODE -ne 0) {
    throw 'Docker Desktop não está rodando.'
}
if (Test-Path 'config/local.php') {
    throw 'Foi encontrado config/local.php. Para Docker, guarde esse arquivo fora do projeto antes de continuar: ele tem prioridade sobre as variáveis DB_* do compose.'
}

Write-Host 'Inicializando VittaPet local (PHP 8.2, MySQL 8 e phpMyAdmin)...'
docker compose -f compose.local.yaml up -d --build
if ($LASTEXITCODE -ne 0) {
    throw 'Falha ao subir containers. Verifique: docker compose -f compose.local.yaml logs db'
}

Write-Host 'Banco iniciado. As migrations 001-009 são importadas somente se o volume estiver vazio.'
if ([string]::IsNullOrWhiteSpace($AdminEmail)) {
    $AdminEmail = Read-Host 'E-mail do administrador para localhost'
}
if (-not [System.Net.Mail.MailAddress]::TryCreate($AdminEmail, [ref]([System.Net.Mail.MailAddress]$null))) {
    # A validação definitiva é realizada pelo script PHP.
    Write-Host 'O formato do e-mail será validado pelo provisionamento.'
}
$secure = Read-Host 'Senha administrativa (mínimo 12 caracteres)' -AsSecureString
$pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
try {
    $plain = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer)
    $env:ADMIN_EMAIL = $AdminEmail
    $env:ADMIN_NAME = $AdminName
    $env:ADMIN_PASSWORD = $plain
    # "-e VAR" passa a variável de ambiente; não exibe senha na linha de comando.
    docker compose -f compose.local.yaml exec -T -e ADMIN_EMAIL -e ADMIN_NAME -e ADMIN_PASSWORD app php scripts/create_admin.php
    if ($LASTEXITCODE -ne 0) {
        throw 'Falha ao criar administrador. Confira se o banco inicializou e se a senha tem pelo menos 12 caracteres.'
    }
}
finally {
    [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer)
    Remove-Item Env:ADMIN_PASSWORD -ErrorAction SilentlyContinue
    Remove-Item Env:ADMIN_EMAIL -ErrorAction SilentlyContinue
    Remove-Item Env:ADMIN_NAME -ErrorAction SilentlyContinue
    Remove-Variable plain -ErrorAction SilentlyContinue
}
Write-Host ''
Write-Host 'Loja:       http://localhost:8080/'
Write-Host 'Admin:      http://localhost:8080/login.php'
Write-Host 'phpMyAdmin: http://localhost:8081/ (vittapet_local / vittapet_dev_only)'
Write-Host 'Lembre-se de cadastrar produtos, WhatsApp, frete e prefixos de CEP no painel.'
