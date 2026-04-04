package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.PasswordResetTokenDto;
import com.sieuthithucung.entity.PasswordResetTokenEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.PasswordResetTokenMapper;
import com.sieuthithucung.repository.PasswordResetTokenRepository;
import com.sieuthithucung.service.PasswordResetTokenService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class PasswordResetTokenServiceImpl implements PasswordResetTokenService {

    private final PasswordResetTokenRepository repository;

    public PasswordResetTokenServiceImpl(PasswordResetTokenRepository repository) {
        this.repository = repository;
    }
    @Override
    public PasswordResetTokenDto create(PasswordResetTokenDto dto) {
        return createInternal(dto);
    }

    @Override
    public PasswordResetTokenDto update(String id, PasswordResetTokenDto dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete(String id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("Password reset token not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public PasswordResetTokenDto findById(String id) {
        return findByIdInternal(id);
    }

    @Override
    public List<PasswordResetTokenDto> getAll() {
        return getAllInternal();
    }

    private PasswordResetTokenDto createInternal(PasswordResetTokenDto dto) {
        PasswordResetTokenEntity entity = PasswordResetTokenMapper.mapToPasswordResetTokenEntity(dto);
        PasswordResetTokenEntity saved = repository.save(entity);
        return PasswordResetTokenMapper.mapToPasswordResetTokenDto(saved);
    }

    private PasswordResetTokenDto updateInternal(String id, PasswordResetTokenDto dto) {
        PasswordResetTokenEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Password reset token not found with id: " + id));

        PasswordResetTokenEntity source = PasswordResetTokenMapper.mapToPasswordResetTokenEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        PasswordResetTokenEntity saved = repository.save(existing);
        return PasswordResetTokenMapper.mapToPasswordResetTokenDto(saved);
    }

    private PasswordResetTokenDto findByIdInternal(String id) {
        PasswordResetTokenEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Password reset token not found with id: " + id));
        return PasswordResetTokenMapper.mapToPasswordResetTokenDto(entity);
    }

    private List<PasswordResetTokenDto> getAllInternal() {
        return repository.findAll().stream().map(PasswordResetTokenMapper::mapToPasswordResetTokenDto).toList();
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