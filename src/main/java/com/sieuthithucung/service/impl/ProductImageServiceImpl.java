package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.ProductImageDto;
import com.sieuthithucung.entity.ProductImageEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.ProductImageMapper;
import com.sieuthithucung.repository.ProductImageRepository;
import com.sieuthithucung.service.ProductImageService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class ProductImageServiceImpl implements ProductImageService {

    private final ProductImageRepository repository;

    public ProductImageServiceImpl(ProductImageRepository repository) {
        this.repository = repository;
    }
    @Override
    public ProductImageDto create(ProductImageDto dto) {
        return createInternal(dto);
    }

    @Override
    public ProductImageDto update(Long id, ProductImageDto dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete(Long id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("Product image not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public ProductImageDto findById(Long id) {
        return findByIdInternal(id);
    }

    @Override
    public List<ProductImageDto> getAll() {
        return getAllInternal();
    }

    private ProductImageDto createInternal(ProductImageDto dto) {
        ProductImageEntity entity = ProductImageMapper.mapToProductImageEntity(dto);
        ProductImageEntity saved = repository.save(entity);
        return ProductImageMapper.mapToProductImageDto(saved);
    }

    private ProductImageDto updateInternal(Long id, ProductImageDto dto) {
        ProductImageEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Product image not found with id: " + id));

        ProductImageEntity source = ProductImageMapper.mapToProductImageEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        ProductImageEntity saved = repository.save(existing);
        return ProductImageMapper.mapToProductImageDto(saved);
    }

    private ProductImageDto findByIdInternal(Long id) {
        ProductImageEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Product image not found with id: " + id));
        return ProductImageMapper.mapToProductImageDto(entity);
    }

    private List<ProductImageDto> getAllInternal() {
        return repository.findAll().stream().map(ProductImageMapper::mapToProductImageDto).toList();
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