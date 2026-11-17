# Instalador local para Windows PowerShell 5.1+ ou PowerShell 7.
# Não apaga volumes nem comita dados privados.
[CmdletBinding()]
param(
    [string]$AdminEmail = '',
    [string]$AdminName = 'Administrador'
)

$ErrorActionPreference = 'Stop'
Set-Location (Resolve-Path (Join-Path $PSScriptRoot '..')).Path

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    throw 'Docker CLI não encontrado. Abra o Docker Desktop.'
}
docker info *> $null
if ($LASTEXITCODE -ne 0) {
    throw 'Docker Desktop não está rodando.'
}
if (Test-Path 'config/local.php') {
    throw 'Existe config/local.php. Guarde esse arquivo fora da pasta do projeto para executar com Docker: ele sobrescreve DB_HOST e as outras variáveis do compose.'
}

Write-Host 'Iniciando PHP 8.2, MySQL 8 e phpMyAdmin...'
docker compose -f compose.local.yaml up -d --build
if ($LASTEXITCODE -ne 0) {
    throw 'Erro no Docker Compose. Veja: docker compose -f compose.local.yaml logs db'
}

Write-Host 'MySQL disponível. Migrations 001-009 são importadas automaticamente na PRIMEIRA inicialização do volume.'
if ([string]::IsNullOrWhiteSpace($AdminEmail)) {
    $AdminEmail = Read-Host 'Informe seu e-mail de login administrativo'
}
if ($AdminEmail -notmatch '^[^\s@]+@[^\s@]+\.[^\s@]+$') {
    throw 'Formato de e-mail inválido.'
}

$secure = Read-Host 'Defina uma senha de administrador (mínimo 12 caracteres)' -AsSecureString
$pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
try {
    $plain = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer)
    $env:ADMIN_EMAIL = $AdminEmail
    $env:ADMIN_NAME = $AdminName
    $env:ADMIN_PASSWORD = $plain

    # -e VAR encaminha variável sem acrescentar o valor à linha de comando do docker.
    docker compose -f compose.local.yaml exec -T -e ADMIN_EMAIL -e ADMIN_NAME -e ADMIN_PASSWORD app php scripts/create_admin.php
    if ($LASTEXITCODE -ne 0) {
        throw 'Falha ao provisionar administrador; revise a senha e o banco local.'
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
Write-Host 'phpMyAdmin: http://localhost:8081/'
Write-Host 'Banco de teste: usuario vittapet_local, senha vittapet_dev_only'
Write-Host 'Configure frete, prefixos de CEP e WhatsApp comercial no painel.'
