param([string]$PhpPath='php',[int]$Port=8766)
$ErrorActionPreference='Stop'
Set-Location $PSScriptRoot
foreach ($taskVariable in @('DB_USER','DB_PASSWORD')) {
 if (-not [Environment]::GetEnvironmentVariable($taskVariable)) { throw "Falta configurar $taskVariable en el entorno del proceso." }
}
$env:ALLOW_LOCAL_HTTP='1'
if (-not $env:APP_RUNTIME) { $env:APP_RUNTIME=Join-Path $PSScriptRoot 'runtime' }
& $PhpPath -d display_errors=0 -S "127.0.0.1:$Port" -t $PSScriptRoot (Join-Path $PSScriptRoot 'router.php')
exit $LASTEXITCODE
