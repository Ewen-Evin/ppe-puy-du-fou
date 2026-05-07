# ============================================================
# start.ps1 - Lance l'API et le back-office Puy du Fou
# Usage : clic droit -> "Executer avec PowerShell"
#         ou depuis un terminal : .\start.ps1
# Prerequis : Laragon doit tourner (pour MySQL)
# ============================================================

$API_PORT  = 8000
$BO_PORT   = 8001
$ROOT      = $PSScriptRoot
$API_INDEX = "$ROOT\V1\api\public\index.php"
$BO_INDEX  = "$ROOT\V1\backoffice\public\index.php"
$API_DIR   = "$ROOT\V1\api\public"
$BO_DIR    = "$ROOT\V1\backoffice\public"
$BO_CONFIG = "$ROOT\V1\backoffice\config\config.php"
$SQL_FILE  = "$ROOT\V1\db\ppe_puy_du_fou.sql"

# --- Recherche de mysql.exe (Laragon ou PATH) ---
$mysqlExe = $null
$laragonPaths = Get-ChildItem "C:\laragon\bin\mysql" -ErrorAction SilentlyContinue |
    Where-Object { $_.PSIsContainer } |
    ForEach-Object { "$($_.FullName)\bin\mysql.exe" } |
    Where-Object { Test-Path $_ }

if ($laragonPaths) {
    $mysqlExe = $laragonPaths | Select-Object -Last 1
} elseif (Get-Command mysql -ErrorAction SilentlyContinue) {
    $mysqlExe = "mysql"
}

# --- Import de la base de donnees ---
Write-Host ""
if ($mysqlExe) {
    Write-Host "  Import de la base de donnees..." -NoNewline
    try {
        & $mysqlExe -u root --execute="source $SQL_FILE" 2>&1 | Out-Null
        Write-Host " OK" -ForegroundColor Green
    } catch {
        Write-Host " ECHEC (continue quand meme)" -ForegroundColor Yellow
    }
} else {
    Write-Host "  mysql.exe introuvable - importe la BDD manuellement via phpMyAdmin." -ForegroundColor Yellow
}

# --- Mise a jour de l'api_base_url dans le back-office ---
$originalConfig = Get-Content $BO_CONFIG -Raw
$updatedConfig  = $originalConfig -replace "('api_base_url'\s*=>\s*)'[^']*'", "'api_base_url' => 'http://localhost:$API_PORT'"
Set-Content $BO_CONFIG $updatedConfig -Encoding UTF8

# --- Lancement des serveurs en arriere-plan ---
Write-Host ""
Write-Host "  Lancement de l'API sur le port $API_PORT ..."
$apiProc = Start-Process php `
    -ArgumentList "-S localhost:$API_PORT -t `"$API_DIR`" `"$API_INDEX`"" `
    -PassThru -WindowStyle Minimized

Write-Host "  Lancement du back-office sur le port $BO_PORT ..."
$boProc = Start-Process php `
    -ArgumentList "-S localhost:$BO_PORT -t `"$BO_DIR`" `"$BO_INDEX`"" `
    -PassThru -WindowStyle Minimized

# --- Attente que l'API reponde ---
Write-Host ""
Write-Host "  Attente de l'API..." -NoNewline
$ready   = $false
$elapsed = 0
$timeout = 30

while (-not $ready -and $elapsed -lt $timeout) {
    Start-Sleep 1
    $elapsed++
    try {
        $null = Invoke-WebRequest "http://localhost:$API_PORT/api/health" -UseBasicParsing -TimeoutSec 2
        $ready = $true
    } catch { }
    Write-Host "." -NoNewline
}

Write-Host ""

if (-not $ready) {
    Write-Host ""
    Write-Host "  ERREUR : l'API ne repond pas apres $timeout secondes." -ForegroundColor Red
    Write-Host "  Verifie que Laragon tourne et que MySQL est actif." -ForegroundColor Red
    Write-Host ""
    Write-Host "  Appuie sur une touche pour quitter..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    $apiProc.Kill()
    $boProc.Kill()
    Set-Content $BO_CONFIG $originalConfig -Encoding UTF8
    exit 1
}

# --- Ouverture du navigateur ---
Start-Process "http://localhost:$API_PORT/api/health"
Start-Sleep 1
Start-Process "http://localhost:$BO_PORT/login"

# --- Infos ---
Write-Host ""
Write-Host "  =================================" -ForegroundColor Green
Write-Host "  Tout est lance !" -ForegroundColor Green
Write-Host "  =================================" -ForegroundColor Green
Write-Host ""
Write-Host "  API         : http://localhost:$API_PORT/api/health"
Write-Host "  Back-office : http://localhost:$BO_PORT/login"
Write-Host ""
Write-Host "  Comptes de test :"
Write-Host "    Gestionnaire : admin@puydufou.fr  /  password"
Write-Host "    Visiteur     : jean.dupont@email.fr  /  password"
Write-Host "    Visiteur     : marie.curie@email.fr  /  password"
Write-Host ""
Write-Host "  Android (emulateur) : http://10.0.2.2:$API_PORT"
Write-Host ""
Write-Host "  Appuie sur une touche pour arreter les serveurs..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

# --- Nettoyage ---
Write-Host ""
Write-Host "  Arret des serveurs..."
$apiProc.Kill()
$boProc.Kill()
Set-Content $BO_CONFIG $originalConfig -Encoding UTF8
Write-Host "  Config restauree. Bye !"
Write-Host ""
