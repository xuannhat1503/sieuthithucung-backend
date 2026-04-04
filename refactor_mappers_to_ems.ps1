$ErrorActionPreference = 'Stop'

$root = 'd:\STTC\Backend\src\main\java\com\sieuthithucung'
$mapperDir = Join-Path $root 'mapper'
$dtoDir = Join-Path $root 'dto'
$entityDir = Join-Path $root 'entity'
$serviceImplDir = Join-Path $root 'service\impl'

$mapperFiles = Get-ChildItem $mapperDir -File -Filter '*Mapper.java' |
  Where-Object { $_.Name -ne 'EntityDtoMapper.java' }

function Get-PrivateFields($filePath) {
  $lines = Get-Content $filePath
  $fields = @()
  foreach ($line in $lines) {
    if ($line -match '^\s*private\s+[^;=]+\s+(\w+)\s*;\s*$') {
      $fields += $matches[1]
    }
  }
  return $fields
}

function To-PascalCase($name) {
  if ([string]::IsNullOrEmpty($name)) { return $name }
  return $name.Substring(0,1).ToUpper() + $name.Substring(1)
}

foreach ($mapper in $mapperFiles) {
  $mapperName = [System.IO.Path]::GetFileNameWithoutExtension($mapper.Name)
  $base = $mapperName.Substring(0, $mapperName.Length - 6) # remove 'Mapper'
  $dtoName = "$base`Dto"
  $entityName = "$base`Entity"

  $dtoPath = Join-Path $dtoDir "$dtoName.java"
  $entityPath = Join-Path $entityDir "$entityName.java"

  if (-not (Test-Path $dtoPath) -or -not (Test-Path $entityPath)) {
    continue
  }

  # Ensure DTO has all-args/no-args constructors for constructor mapping style.
  $dtoText = Get-Content $dtoPath -Raw
  if ($dtoText -notmatch 'import lombok\.AllArgsConstructor;') {
    $dtoText = $dtoText -replace 'import lombok\.Data;\r?\n', "import lombok.Data;`r`nimport lombok.NoArgsConstructor;`r`nimport lombok.AllArgsConstructor;`r`n"
  }
  if ($dtoText -notmatch '@NoArgsConstructor') {
    $dtoText = $dtoText -replace '@Data', "@Data`r`n@NoArgsConstructor`r`n@AllArgsConstructor"
  }
  Set-Content $dtoPath $dtoText -NoNewline

  $fields = Get-PrivateFields $dtoPath

  $dtoArgs = @()
  $entityArgs = @()
  foreach ($f in $fields) {
    $m = To-PascalCase $f
    $dtoArgs += "                entity.get$m()"
    $entityArgs += "                dto.get$m()"
  }

  $dtoArgText = [string]::Join(",`r`n", $dtoArgs)
  $entityArgText = [string]::Join(",`r`n", $entityArgs)

  $newMapper = @"
package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.$dtoName;
import com.sieuthithucung.entity.$entityName;

public class $mapperName {
    public static $dtoName mapTo$dtoName($entityName entity) {
        return new $dtoName(
$dtoArgText
        );
    }

    public static $entityName mapTo$entityName($dtoName dto) {
        return new $entityName(
$entityArgText
        );
    }
}
"@

  Set-Content $mapper.FullName $newMapper -NoNewline

  # Update CRUD service implementation constructor to static mapper method refs.
  $serviceImplPath = Join-Path $serviceImplDir "$base`ServiceImpl.java"
  if (Test-Path $serviceImplPath) {
    $svc = Get-Content $serviceImplPath -Raw
    $svc = $svc -replace ",\s*$mapperName\s+mapper\)", ")"
    $replacement = ('super(repository, {0}::mapTo{1}, {0}::mapTo{2}, "' -f $mapperName, $entityName, $dtoName)
    $svc = $svc -replace 'super\(repository,\s*mapper,\s*"', $replacement
    Set-Content $serviceImplPath $svc -NoNewline
  }
}

# Remove mapper interface if no longer referenced.
$allJava = Get-ChildItem $root -Recurse -File -Filter '*.java'
$usesEntityMapper = $false
foreach ($j in $allJava) {
  $txt = Get-Content $j.FullName -Raw
  if ($txt -match 'EntityDtoMapper') {
    if ($j.FullName -notlike '*\mapper\EntityDtoMapper.java') {
      $usesEntityMapper = $true
      break
    }
  }
}

if (-not $usesEntityMapper) {
  $entityMapperPath = Join-Path $mapperDir 'EntityDtoMapper.java'
  if (Test-Path $entityMapperPath) { Remove-Item $entityMapperPath -Force }
}
