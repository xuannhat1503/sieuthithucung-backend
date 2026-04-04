package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.RoleDto;
import com.sieuthithucung.entity.RoleEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.RoleMapper;
import com.sieuthithucung.repository.RoleRepository;
import com.sieuthithucung.service.RoleService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class RoleServiceImpl implements RoleService {

    private final RoleRepository repository;

    public RoleServiceImpl(RoleRepository repository) {
        this.repository = repository;
    }
    @Override
    public RoleDto create(RoleDto dto) {
        return createInternal(dto);
    }

    @Override
    public RoleDto update(Long id, RoleDto dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete(Long id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("Role not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public RoleDto findById(Long id) {
        return findByIdInternal(id);
    }

    @Override
    public List<RoleDto> getAll() {
        return getAllInternal();
    }

    private RoleDto createInternal(RoleDto dto) {
        RoleEntity entity = RoleMapper.mapToRoleEntity(dto);
        RoleEntity saved = repository.save(entity);
        return RoleMapper.mapToRoleDto(saved);
    }

    private RoleDto updateInternal(Long id, RoleDto dto) {
        RoleEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Role not found with id: " + id));

        RoleEntity source = RoleMapper.mapToRoleEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        RoleEntity saved = repository.save(existing);
        return RoleMapper.mapToRoleDto(saved);
    }

    private RoleDto findByIdInternal(Long id) {
        RoleEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Role not found with id: " + id));
        return RoleMapper.mapToRoleDto(entity);
    }

    private List<RoleDto> getAllInternal() {
        return repository.findAll().stream().map(RoleMapper::mapToRoleDto).toList();
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