param(
    [string]$ClinicPath = 'C:\xampp\htdocs\gperp-clinic',
    [string]$ModelsPath = '',
    [int]$GatewayPort = 8080,
    [int]$ClinicPort = 8000,
    [switch]$UseGpu
)
$ErrorActionPreference = 'Stop'
$gatewayPath = Split-Path $PSScriptRoot -Parent
$php = (Get-Command php -ErrorAction Stop).Source
$ollama = (Get-Command ollama -ErrorAction Stop).Source
foreach ($path in @($gatewayPath, $ClinicPath)) {
    if (-not (Test-Path -LiteralPath (Join-Path $path 'vendor\autoload.php'))) {
        throw "Dependencies are missing in $path"
    }
    if (-not (Test-Path -LiteralPath (Join-Path $path '.env'))) {
        throw "Configure the server-side environment in $path first."
    }
}
if (-not (Get-NetTCPConnection -LocalPort 11434 -State Listen -ErrorAction SilentlyContinue)) {
    if (-not $UseGpu) { $env:CUDA_VISIBLE_DEVICES = '-1' }
    if ($ModelsPath) {
        if (-not (Test-Path -LiteralPath $ModelsPath -PathType Container)) { throw 'ModelsPath does not exist.' }
        $env:OLLAMA_MODELS = (Resolve-Path -LiteralPath $ModelsPath).Path
    }
    $env:OLLAMA_MAX_LOADED_MODELS = '1'
    $env:OLLAMA_NUM_PARALLEL = '1'
    $env:OLLAMA_CONTEXT_LENGTH = '4096'
    Start-Process -FilePath $ollama -ArgumentList 'serve' -WindowStyle Hidden `
        -RedirectStandardOutput (Join-Path $gatewayPath 'storage\logs\local-ollama-cpu.out.log') `
        -RedirectStandardError (Join-Path $gatewayPath 'storage\logs\local-ollama-cpu.err.log')
}
foreach ($application in @(@{Path=$gatewayPath; Port=$GatewayPort; Name='gateway'}, @{Path=$ClinicPath; Port=$ClinicPort; Name='clinic'})) {
    if (-not (Get-NetTCPConnection -LocalPort $application.Port -State Listen -ErrorAction SilentlyContinue)) {
        Start-Process -FilePath $php -ArgumentList @('artisan', 'serve', '--host=127.0.0.1', "--port=$($application.Port)") `
            -WorkingDirectory $application.Path -WindowStyle Hidden `
            -RedirectStandardOutput (Join-Path $application.Path "storage\logs\local-$($application.Name)-server.out.log") `
            -RedirectStandardError (Join-Path $application.Path "storage\logs\local-$($application.Name)-server.err.log")
    }
}
Write-Output "Clinic: http://127.0.0.1:$ClinicPort"
Write-Output "Gateway: http://127.0.0.1:$GatewayPort"
Write-Output 'Existing listeners were preserved. Run gateway:readiness and the opt-in live acceptance test to verify service identity and inference.'
Write-Output 'This starts local development servers. It does not configure Windows services or production queue/scheduler supervision.'