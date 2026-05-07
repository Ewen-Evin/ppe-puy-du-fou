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

# --- Recherche de php.exe (Laragon ou PATH) ---
$phpExe = $null
$phpPaths = Get-ChildItem "C:\laragon\bin\php" -ErrorAction SilentlyContinue |
    Where-Object { $_.PSIsContainer } |
    ForEach-Object { "$($_.FullName)\php.exe" } |
    Where-Object { Test-Path $_ }

if ($phpPaths) {
    $phpExe = $phpPaths | Select-Object -Last 1
} elseif (Get-Command php -ErrorAction SilentlyContinue) {
    $phpExe = (Get-Command php).Source
}

if (-not $phpExe) {
    Write-Host "  ERREUR : php.exe introuvable. Verifie que Laragon est installe." -ForegroundColor Red
    exit 1
}

# --- Recherche de Chrome ---
$chromeExe = @(
    "$env:ProgramFiles\Google\Chrome\Application\chrome.exe",
    "${env:ProgramFiles(x86)}\Google\Chrome\Application\chrome.exe",
    "$env:LOCALAPPDATA\Google\Chrome\Application\chrome.exe"
) | Where-Object { Test-Path $_ } | Select-Object -First 1

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
$utf8NoBom      = New-Object System.Text.UTF8Encoding $false
$originalConfig = Get-Content $BO_CONFIG -Raw
$updatedConfig  = $originalConfig -replace "('api_base_url'\s*=>\s*)'[^']*'", "'api_base_url' => 'http://localhost:$API_PORT'"
[System.IO.File]::WriteAllText($BO_CONFIG, $updatedConfig, $utf8NoBom)

# --- Lancement des serveurs en arriere-plan ---
$apiLog = "$ROOT\api_server.log"
$boLog  = "$ROOT\bo_server.log"

Write-Host ""
Write-Host "  Lancement de l'API sur le port $API_PORT ..."
$apiProc = Start-Process $phpExe `
    -ArgumentList "-S 0.0.0.0:$API_PORT -t `"$API_DIR`" `"$API_INDEX`"" `
    -RedirectStandardError $apiLog -WindowStyle Hidden -PassThru

Write-Host "  Lancement du back-office sur le port $BO_PORT ..."
$boProc = Start-Process $phpExe `
    -ArgumentList "-S localhost:$BO_PORT -t `"$BO_DIR`" `"$BO_INDEX`"" `
    -RedirectStandardError $boLog -WindowStyle Hidden -PassThru

Start-Sleep 2
if ($apiProc.HasExited) {
    Write-Host ""
    Write-Host "  ERREUR : l'API a crashe au demarrage. Log :" -ForegroundColor Red
    Get-Content $apiLog -ErrorAction SilentlyContinue | ForEach-Object { Write-Host "    $_" -ForegroundColor Red }
    [System.IO.File]::WriteAllText($BO_CONFIG, $originalConfig, $utf8NoBom)
    exit 1
}
if ($boProc.HasExited) {
    Write-Host ""
    Write-Host "  ERREUR : le back-office a crashe au demarrage. Log :" -ForegroundColor Red
    Get-Content $boLog -ErrorAction SilentlyContinue | ForEach-Object { Write-Host "    $_" -ForegroundColor Red }
    $apiProc.Kill()
    [System.IO.File]::WriteAllText($BO_CONFIG, $originalConfig, $utf8NoBom)
    exit 1
}

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
    [System.IO.File]::WriteAllText($BO_CONFIG, $originalConfig, $utf8NoBom)
    exit 1
}

# --- Ouverture du navigateur ---
if ($chromeExe) {
    Start-Process $chromeExe "http://localhost:$API_PORT/api/health"
    Start-Sleep 1
    Start-Process $chromeExe "http://localhost:$BO_PORT/login"
} else {
    Start-Process "http://localhost:$API_PORT/api/health"
    Start-Sleep 1
    Start-Process "http://localhost:$BO_PORT/login"
}

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
if (-not $apiProc.HasExited) { $apiProc.Kill() }
if (-not $boProc.HasExited)  { $boProc.Kill() }
Remove-Item $apiLog, $boLog -ErrorAction SilentlyContinue
[System.IO.File]::WriteAllText($BO_CONFIG, $originalConfig, $utf8NoBom)
Write-Host "  Config restauree. Bye !"
Write-Host ""
