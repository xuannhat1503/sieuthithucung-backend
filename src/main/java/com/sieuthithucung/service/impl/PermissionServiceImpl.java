package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.PermissionDto;
import com.sieuthithucung.entity.PermissionEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.PermissionMapper;
import com.sieuthithucung.repository.PermissionRepository;
import com.sieuthithucung.service.PermissionService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class PermissionServiceImpl implements PermissionService {

    private final PermissionRepository repository;

    public PermissionServiceImpl(PermissionRepository repository) {
        this.repository = repository;
    }
    @Override
    public PermissionDto create(PermissionDto dto) {
        return createInternal(dto);
    }

    @Override
    public PermissionDto update(Long id, PermissionDto dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete(Long id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("Permission not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public PermissionDto findById(Long id) {
        return findByIdInternal(id);
    }

    @Override
    public List<PermissionDto> getAll() {
        return getAllInternal();
    }

    private PermissionDto createInternal(PermissionDto dto) {
        PermissionEntity entity = PermissionMapper.mapToPermissionEntity(dto);
        PermissionEntity saved = repository.save(entity);
        return PermissionMapper.mapToPermissionDto(saved);
    }

    private PermissionDto updateInternal(Long id, PermissionDto dto) {
        PermissionEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Permission not found with id: " + id));

        PermissionEntity source = PermissionMapper.mapToPermissionEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        PermissionEntity saved = repository.save(existing);
        return PermissionMapper.mapToPermissionDto(saved);
    }

    private PermissionDto findByIdInternal(Long id) {
        PermissionEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Permission not found with id: " + id));
        return PermissionMapper.mapToPermissionDto(entity);
    }

    private List<PermissionDto> getAllInternal() {
        return repository.findAll().stream().map(PermissionMapper::mapToPermissionDto).toList();
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
}