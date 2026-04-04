package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.RolePermissionDto;
import com.sieuthithucung.entity.RolePermissionEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.RolePermissionMapper;
import com.sieuthithucung.repository.RolePermissionRepository;
import com.sieuthithucung.service.RolePermissionService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class RolePermissionServiceImpl implements RolePermissionService {

    private final RolePermissionRepository repository;

    public RolePermissionServiceImpl(RolePermissionRepository repository) {
        this.repository = repository;
    }
    @Override
    public RolePermissionDto create(RolePermissionDto dto) {
        return createInternal(dto);
    }

    @Override
    public RolePermissionDto update(Long id, RolePermissionDto dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete(Long id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("Role permission not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public RolePermissionDto findById(Long id) {
        return findByIdInternal(id);
    }

    @Override
    public List<RolePermissionDto> getAll() {
        return getAllInternal();
    }

    private RolePermissionDto createInternal(RolePermissionDto dto) {
        RolePermissionEntity entity = RolePermissionMapper.mapToRolePermissionEntity(dto);
        RolePermissionEntity saved = repository.save(entity);
        return RolePermissionMapper.mapToRolePermissionDto(saved);
    }

    private RolePermissionDto updateInternal(Long id, RolePermissionDto dto) {
        RolePermissionEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Role permission not found with id: " + id));

        RolePermissionEntity source = RolePermissionMapper.mapToRolePermissionEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        RolePermissionEntity saved = repository.save(existing);
        return RolePermissionMapper.mapToRolePermissionDto(saved);
    }

    private RolePermissionDto findByIdInternal(Long id) {
        RolePermissionEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Role permission not found with id: " + id));
        return RolePermissionMapper.mapToRolePermissionDto(entity);
    }

    private List<RolePermissionDto> getAllInternal() {
        return repository.findAll().stream().map(RolePermissionMapper::mapToRolePermissionDto).toList();
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