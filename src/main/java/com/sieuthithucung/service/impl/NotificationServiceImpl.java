package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.NotificationDto;
import com.sieuthithucung.entity.NotificationEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.NotificationMapper;
import com.sieuthithucung.repository.NotificationRepository;
import com.sieuthithucung.service.NotificationService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class NotificationServiceImpl implements NotificationService {

    private final NotificationRepository repository;

    public NotificationServiceImpl(NotificationRepository repository) {
        this.repository = repository;
    }
    @Override
    public NotificationDto create(NotificationDto dto) {
        return createInternal(dto);
    }

    @Override
    public NotificationDto update(Long id, NotificationDto dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete(Long id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("Notification not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public NotificationDto findById(Long id) {
        return findByIdInternal(id);
    }

    @Override
    public List<NotificationDto> getAll() {
        return getAllInternal();
    }

    private NotificationDto createInternal(NotificationDto dto) {
        NotificationEntity entity = NotificationMapper.mapToNotificationEntity(dto);
        NotificationEntity saved = repository.save(entity);
        return NotificationMapper.mapToNotificationDto(saved);
    }

    private NotificationDto updateInternal(Long id, NotificationDto dto) {
        NotificationEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Notification not found with id: " + id));

        NotificationEntity source = NotificationMapper.mapToNotificationEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        NotificationEntity saved = repository.save(existing);
        return NotificationMapper.mapToNotificationDto(saved);
    }

    private NotificationDto findByIdInternal(Long id) {
        NotificationEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Notification not found with id: " + id));
        return NotificationMapper.mapToNotificationDto(entity);
    }

    private List<NotificationDto> getAllInternal() {
        return repository.findAll().stream().map(NotificationMapper::mapToNotificationDto).toList();
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