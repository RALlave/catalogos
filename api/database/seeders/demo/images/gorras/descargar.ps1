# Descarga las fotos de las gorras en esta misma carpeta.
# Uso: clic derecho sobre el archivo -> Ejecutar con PowerShell

$ErrorActionPreference = 'Continue'
$destino = Split-Path -Parent $MyInvocation.MyCommand.Path
$ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
$ok = 0; $fallos = 0

Write-Host "Descargando en: $destino" -ForegroundColor Cyan

Write-Host '  gorra-velvet-5-panel-bicolor'
$d = Join-Path $destino 'gorra-velvet-5-panel-bicolor-01.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/Hc61659020762463280b131994ca0b422z.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-velvet-5-panel-bicolor-01.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-velvet-5-panel-bicolor-02.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H8351e4c3922c42f49040792450fa32049.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-velvet-5-panel-bicolor-02.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-velvet-5-panel-bicolor-03.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H18109dedc8054430a8fd14d98bebadc2r.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-velvet-5-panel-bicolor-03.jpg' -ForegroundColor Red; $fallos++ }

Write-Host '  gorra-trucker-velvet-malla'
$d = Join-Path $destino 'gorra-trucker-velvet-malla-01.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H9839255e79a74b869fa1f3939214c350t.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-trucker-velvet-malla-01.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-trucker-velvet-malla-02.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H3a5d5ab71a744ab294d4f274c2c1b246O.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-trucker-velvet-malla-02.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-trucker-velvet-malla-03.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/HTB1sndZhgvD8KJjy0Flq6ygBFXa8.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-trucker-velvet-malla-03.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-trucker-velvet-malla-04.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/Hebc06452f99d4547a14bbf774a91e2b0g.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-trucker-velvet-malla-04.jpg' -ForegroundColor Red; $fallos++ }

Write-Host '  gorra-algodon-twill-6-panel'
$d = Join-Path $destino 'gorra-algodon-twill-6-panel-01.jpg'
try { Invoke-WebRequest -Uri 'https://sc01.alicdn.com/kf/H5a406e3ea7074911a1d9f4c2eb9a566ef.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-algodon-twill-6-panel-01.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-algodon-twill-6-panel-02.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H6260d88d65334cb3bb37924530eae47bq.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-algodon-twill-6-panel-02.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-algodon-twill-6-panel-03.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H69c99929372e4fe18f6f66466ec0c2d4h.png_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-algodon-twill-6-panel-03.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-algodon-twill-6-panel-04.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H6dcc6018674242ae98fb7aafd1f07eafm.png_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-algodon-twill-6-panel-04.jpg' -ForegroundColor Red; $fallos++ }

Write-Host '  gorra-denim-desgastada-bordada'
$d = Join-Path $destino 'gorra-denim-desgastada-bordada-01.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H89d5331c33584bf183729d0a3e1acc43n.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-denim-desgastada-bordada-01.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-denim-desgastada-bordada-02.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H9512288a047f47e882f558697e3779e3j.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-denim-desgastada-bordada-02.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-denim-desgastada-bordada-03.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H6937145d780048fa9a1ace714a774014D.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-denim-desgastada-bordada-03.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-denim-desgastada-bordada-04.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H04734a4d88df461c81d1661034b3728c3.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-denim-desgastada-bordada-04.jpg' -ForegroundColor Red; $fallos++ }

Write-Host '  gorra-denim-lavada-6-panel'
$d = Join-Path $destino 'gorra-denim-lavada-6-panel-01.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H17a02fdabdea43e0a52c2f11ef835210i.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-denim-lavada-6-panel-01.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-denim-lavada-6-panel-02.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H0144b1c97904442b9b2031f071b15094h.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-denim-lavada-6-panel-02.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-denim-lavada-6-panel-03.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H98f9169968eb41bf970764cd309c5ad1D.png_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-denim-lavada-6-panel-03.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-denim-lavada-6-panel-04.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H6a48747023dd4e99a9cc281f86916e38J.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-denim-lavada-6-panel-04.jpg' -ForegroundColor Red; $fallos++ }

Write-Host '  gorra-lisa-5-panel-deportiva'
$d = Join-Path $destino 'gorra-lisa-5-panel-deportiva-01.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/Hfe2abfc07ebe48f1bbdaca36343dddfdQ.png_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-lisa-5-panel-deportiva-01.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-lisa-5-panel-deportiva-02.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/Hde85e25bd79f43668b4632ab16d3067do.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-lisa-5-panel-deportiva-02.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-lisa-5-panel-deportiva-03.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/Hd8967e5ca9674918beeed6bae2317ff4f.png_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-lisa-5-panel-deportiva-03.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-lisa-5-panel-deportiva-04.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H2104356573e04954b4a25f9f90b757caG.png_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-lisa-5-panel-deportiva-04.jpg' -ForegroundColor Red; $fallos++ }

Write-Host '  gorra-rayas-bordado-3d'
$d = Join-Path $destino 'gorra-rayas-bordado-3d-01.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H00dc870c3dce4c6e8b51ec8ec50883c4t.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-rayas-bordado-3d-01.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-rayas-bordado-3d-02.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H9bc83f69a5df481e9412b2c8710a0682K.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-rayas-bordado-3d-02.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-rayas-bordado-3d-03.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H63dd2fe693b54ee198dd030de9e9f60aQ.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-rayas-bordado-3d-03.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-rayas-bordado-3d-04.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H9881bb36edc24da1855d7ddd684260c6d.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-rayas-bordado-3d-04.jpg' -ForegroundColor Red; $fallos++ }

Write-Host '  gorra-running-upf50'
$d = Join-Path $destino 'gorra-running-upf50-01.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/Hcc16b95a3e00472c9e4ddbfb00710b83B.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-running-upf50-01.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-running-upf50-02.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/Hf73ff0692f38486eb7c654b20a8c026ep.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-running-upf50-02.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-running-upf50-03.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H78ada6c0994341998e55a8cb2e8ad8bfb.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-running-upf50-03.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-running-upf50-04.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/He1456adb6f9746f4aba9244f82641e31z.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-running-upf50-04.jpg' -ForegroundColor Red; $fallos++ }

Write-Host '  gorra-unstructured-bordada'
$d = Join-Path $destino 'gorra-unstructured-bordada-01.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/Hb7470de47f3b48baa9774147d02fdbf7D.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-unstructured-bordada-01.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-unstructured-bordada-02.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H7e468c06e80f4983a46a0488fe5dddf5n.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-unstructured-bordada-02.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-unstructured-bordada-03.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/Ha4c8fe7bcbd9434da48f91cd297d70e2V.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-unstructured-bordada-03.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-unstructured-bordada-04.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H74b8421b3ad844c184080aca95ead72aS.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-unstructured-bordada-04.jpg' -ForegroundColor Red; $fallos++ }

Write-Host '  gorra-bicolor-6-panel-estructurada'
$d = Join-Path $destino 'gorra-bicolor-6-panel-estructurada-01.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/Hc2c1352793af46e4bc93bf3d84b7a023D.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-bicolor-6-panel-estructurada-01.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-bicolor-6-panel-estructurada-02.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/Hf50e27de88d94646b52101f5eefcab39F.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-bicolor-6-panel-estructurada-02.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-bicolor-6-panel-estructurada-03.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/Haff29d73d85e4186a33feeda659c33abB.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-bicolor-6-panel-estructurada-03.jpg' -ForegroundColor Red; $fallos++ }
$d = Join-Path $destino 'gorra-bicolor-6-panel-estructurada-04.jpg'
try { Invoke-WebRequest -Uri 'https://s.alicdn.com/@sc04/kf/H76127037b90f4a40a6df2b08b06480b8C.jpg_960x960q80.jpg' -OutFile $d -UserAgent $ua -TimeoutSec 30; $ok++ }
catch { Write-Host '    FALLO: gorra-bicolor-6-panel-estructurada-04.jpg' -ForegroundColor Red; $fallos++ }

Write-Host ''
Write-Host "Listo: $ok descargadas, $fallos fallidas." -ForegroundColor Green
Write-Host 'Avisale a Claude y segui con el JSON.'
Read-Host 'Enter para cerrar'