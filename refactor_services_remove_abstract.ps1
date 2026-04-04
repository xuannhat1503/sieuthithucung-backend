$ErrorActionPreference = 'Stop'

$implDir = 'd:\STTC\Backend\src\main\java\com\sieuthithucung\service\impl'
$files = Get-ChildItem $implDir -File -Filter '*ServiceImpl.java'

foreach ($f in $files) {
  $text = Get-Content $f.FullName -Raw
  if ($text -notmatch 'extends\s+AbstractCrudService<') {
    continue
  }

  if ($text -notmatch 'public\s+class\s+(\w+)\s+extends\s+AbstractCrudService<\s*([^,>]+)\s*,\s*([^,>]+)\s*,\s*([^>]+)\s*>\s+implements\s+(\w+)') {
    continue
  }

  $className = $matches[1]
  $entityType = $matches[2]
  $idType = $matches[3]
  $dtoType = $matches[4]
  $serviceInterface = $matches[5]

  if ($text -notmatch 'import\s+com\.sieuthithucung\.repository\.(\w+);') { continue }
  $repositoryType = $matches[1]

  if ($text -notmatch 'import\s+com\.sieuthithucung\.mapper\.(\w+);') { continue }
  $mapperType = $matches[1]

  if ($text -notmatch 'super\(repository,\s*\w+::\w+,\s*\w+::\w+,\s*"([^"]+)"\)') { continue }
  $resourceName = $matches[1]

  $isUser = $className -eq 'UserServiceImpl'

  $userMethods = ''
  if ($isUser) {
    $userMethods = @"

    @Override
    public UserDto create(UserDto dto) {
        return sanitize(createInternal(dto));
    }

    @Override
    public UserDto update(Long id, UserDto dto) {
        return sanitize(updateInternal(id, dto));
    }

    @Override
    public UserDto findById(Long id) {
        return sanitize(findByIdInternal(id));
    }

    @Override
    public List<UserDto> getAll() {
        return getAllInternal().stream().map(this::sanitize).toList();
    }

    private UserDto sanitize(UserDto dto) {
        if (dto != null) {
            dto.setPassword(null);
        }
        return dto;
    }
"@
  }

  $crudMethods = @"

    @Override
    public $dtoType create($dtoType dto) {
        return createInternal(dto);
    }

    @Override
    public $dtoType update($idType id, $dtoType dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete($idType id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("$resourceName not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public $dtoType findById($idType id) {
        return findByIdInternal(id);
    }

    @Override
    public List<$dtoType> getAll() {
        return getAllInternal();
    }

    private $dtoType createInternal($dtoType dto) {
        $entityType entity = $mapperType.mapTo$entityType(dto);
        $entityType saved = repository.save(entity);
        return $mapperType.mapTo$dtoType(saved);
    }

    private $dtoType updateInternal($idType id, $dtoType dto) {
        $entityType existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("$resourceName not found with id: " + id));

        $entityType source = $mapperType.mapTo$entityType(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        $entityType saved = repository.save(existing);
        return $mapperType.mapTo$dtoType(saved);
    }

    private $dtoType findByIdInternal($idType id) {
        $entityType entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("$resourceName not found with id: " + id));
        return $mapperType.mapTo$dtoType(entity);
    }

    private List<$dtoType> getAllInternal() {
        return repository.findAll().stream().map($mapperType::mapTo$dtoType).toList();
    }

    private String[] getNullPropertyNames(Object source) {
        BeanWrapper src = new BeanWrapperImpl(source);
        PropertyDescriptor[] pds = src.getPropertyDescriptors();

        Set<String> emptyNames = new HashSet<>();
        for (PropertyDescriptor pd : pds) {
            Object srcValue = src.getPropertyValue(pd.getName());
            if (srcValue == null) {
                emptyNames.add(pd.getName());
            }
        }
        return emptyNames.toArray(new String[0]);
    }
"@

  if ($isUser) {
    $methods = $userMethods + $crudMethods -replace "@Override\s+public\s+$dtoType\s+create\(\$dtoType dto\)\s+\{\s+return createInternal\(dto\);\s+\}\s+\s+@Override\s+public\s+$dtoType\s+update\(\$idType id, \$dtoType dto\)\s+\{\s+return updateInternal\(id, dto\);\s+\}\s+\s+@Override\s+public\s+$dtoType\s+findById\(\$idType id\)\s+\{\s+return findByIdInternal\(id\);\s+\}\s+\s+@Override\s+public\s+List<\$dtoType>\s+getAll\(\)\s+\{\s+return getAllInternal\(\);\s+\}", ''
  }
  else {
    $methods = $crudMethods
  }

  $newText = @"
package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.$dtoType;
import com.sieuthithucung.entity.$entityType;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.$mapperType;
import com.sieuthithucung.repository.$repositoryType;
import com.sieuthithucung.service.$serviceInterface;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class $className implements $serviceInterface {

    private final $repositoryType repository;

    public $className($repositoryType repository) {
        this.repository = repository;
    }$methods
}
"@

  Set-Content -Path $f.FullName -Value $newText -NoNewline
}
