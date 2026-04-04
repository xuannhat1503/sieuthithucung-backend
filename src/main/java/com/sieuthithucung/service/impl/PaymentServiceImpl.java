package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.PaymentDto;
import com.sieuthithucung.entity.PaymentEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.PaymentMapper;
import com.sieuthithucung.repository.PaymentRepository;
import com.sieuthithucung.service.PaymentService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class PaymentServiceImpl implements PaymentService {

    private final PaymentRepository repository;

    public PaymentServiceImpl(PaymentRepository repository) {
        this.repository = repository;
    }
    @Override
    public PaymentDto create(PaymentDto dto) {
        return createInternal(dto);
    }

    @Override
    public PaymentDto update(Long id, PaymentDto dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete(Long id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("Payment not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public PaymentDto findById(Long id) {
        return findByIdInternal(id);
    }

    @Override
    public List<PaymentDto> getAll() {
        return getAllInternal();
    }

    private PaymentDto createInternal(PaymentDto dto) {
        PaymentEntity entity = PaymentMapper.mapToPaymentEntity(dto);
        PaymentEntity saved = repository.save(entity);
        return PaymentMapper.mapToPaymentDto(saved);
    }

    private PaymentDto updateInternal(Long id, PaymentDto dto) {
        PaymentEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Payment not found with id: " + id));

        PaymentEntity source = PaymentMapper.mapToPaymentEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        PaymentEntity saved = repository.save(existing);
        return PaymentMapper.mapToPaymentDto(saved);
    }

    private PaymentDto findByIdInternal(Long id) {
        PaymentEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Payment not found with id: " + id));
        return PaymentMapper.mapToPaymentDto(entity);
    }

    private List<PaymentDto> getAllInternal() {
        return repository.findAll().stream().map(PaymentMapper::mapToPaymentDto).toList();
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