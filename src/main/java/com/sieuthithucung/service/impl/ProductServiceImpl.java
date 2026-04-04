package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.ProductDto;
import com.sieuthithucung.entity.ProductEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.ProductMapper;
import com.sieuthithucung.repository.ProductRepository;
import com.sieuthithucung.service.ProductService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class ProductServiceImpl implements ProductService {

    private final ProductRepository repository;

    public ProductServiceImpl(ProductRepository repository) {
        this.repository = repository;
    }
    @Override
    public ProductDto create(ProductDto dto) {
        return createInternal(dto);
    }

    @Override
    public ProductDto update(Long id, ProductDto dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete(Long id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("Product not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public ProductDto findById(Long id) {
        return findByIdInternal(id);
    }

    @Override
    public List<ProductDto> getAll() {
        return getAllInternal();
    }

    private ProductDto createInternal(ProductDto dto) {
        ProductEntity entity = ProductMapper.mapToProductEntity(dto);
        ProductEntity saved = repository.save(entity);
        return ProductMapper.mapToProductDto(saved);
    }

    private ProductDto updateInternal(Long id, ProductDto dto) {
        ProductEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Product not found with id: " + id));

        ProductEntity source = ProductMapper.mapToProductEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        ProductEntity saved = repository.save(existing);
        return ProductMapper.mapToProductDto(saved);
    }

    private ProductDto findByIdInternal(Long id) {
        ProductEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Product not found with id: " + id));
        return ProductMapper.mapToProductDto(entity);
    }

    private List<ProductDto> getAllInternal() {
        return repository.findAll().stream().map(ProductMapper::mapToProductDto).toList();
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