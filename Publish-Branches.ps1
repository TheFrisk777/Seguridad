$ErrorActionPreference='Stop'
Set-Location $PSScriptRoot
$taskRemote=(& git remote get-url origin).Trim()
if ($taskRemote -ne 'https://github.com/TheFrisk777/Seguridad.git') { throw 'El remoto no coincide con el repositorio autorizado.' }
& git push origin unidad-ii-base unidad-ii-corregida
if ($LASTEXITCODE -ne 0) { throw 'No se pudieron publicar las ramas. Revisar la autenticación GitHub.' }
foreach ($taskBranch in @('unidad-ii-base','unidad-ii-corregida')) {
 $taskLocal=(& git rev-parse $taskBranch).Trim()
 $taskLine=& git ls-remote origin "refs/heads/$taskBranch"
 if ($LASTEXITCODE -ne 0 -or -not $taskLine -or (($taskLine -split '\s+')[0] -ne $taskLocal)) { throw "No se pudo verificar $taskBranch." }
 Write-Output "$taskBranch publicada y verificada: $taskLocal"
}
