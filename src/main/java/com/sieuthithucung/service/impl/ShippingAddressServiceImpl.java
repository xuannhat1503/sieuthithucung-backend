package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.ShippingAddressDto;
import com.sieuthithucung.entity.ShippingAddressEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.ShippingAddressMapper;
import com.sieuthithucung.repository.ShippingAddressRepository;
import com.sieuthithucung.service.ShippingAddressService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class ShippingAddressServiceImpl implements ShippingAddressService {

    private final ShippingAddressRepository repository;

    public ShippingAddressServiceImpl(ShippingAddressRepository repository) {
        this.repository = repository;
    }
    @Override
    public ShippingAddressDto create(ShippingAddressDto dto) {
        return createInternal(dto);
    }

    @Override
    public ShippingAddressDto update(Long id, ShippingAddressDto dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete(Long id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("Shipping address not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public ShippingAddressDto findById(Long id) {
        return findByIdInternal(id);
    }

    @Override
    public List<ShippingAddressDto> getAll() {
        return getAllInternal();
    }

    private ShippingAddressDto createInternal(ShippingAddressDto dto) {
        ShippingAddressEntity entity = ShippingAddressMapper.mapToShippingAddressEntity(dto);
        ShippingAddressEntity saved = repository.save(entity);
        return ShippingAddressMapper.mapToShippingAddressDto(saved);
    }

    private ShippingAddressDto updateInternal(Long id, ShippingAddressDto dto) {
        ShippingAddressEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Shipping address not found with id: " + id));

        ShippingAddressEntity source = ShippingAddressMapper.mapToShippingAddressEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        ShippingAddressEntity saved = repository.save(existing);
        return ShippingAddressMapper.mapToShippingAddressDto(saved);
    }

    private ShippingAddressDto findByIdInternal(Long id) {
        ShippingAddressEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Shipping address not found with id: " + id));
        return ShippingAddressMapper.mapToShippingAddressDto(entity);
    }

    private List<ShippingAddressDto> getAllInternal() {
        return repository.findAll().stream().map(ShippingAddressMapper::mapToShippingAddressDto).toList();
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