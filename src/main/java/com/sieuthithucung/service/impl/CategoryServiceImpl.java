package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.CategoryDto;
import com.sieuthithucung.entity.CategoryEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.CategoryMapper;
import com.sieuthithucung.repository.CategoryRepository;
import com.sieuthithucung.service.CategoryService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class CategoryServiceImpl implements CategoryService {

    private final CategoryRepository repository;

    public CategoryServiceImpl(CategoryRepository repository) {
        this.repository = repository;
    }
    @Override
    public CategoryDto create(CategoryDto dto) {
        return createInternal(dto);
    }

    @Override
    public CategoryDto update(Long id, CategoryDto dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete(Long id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("Category not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public CategoryDto findById(Long id) {
        return findByIdInternal(id);
    }

    @Override
    public List<CategoryDto> getAll() {
        return getAllInternal();
    }

    private CategoryDto createInternal(CategoryDto dto) {
        CategoryEntity entity = CategoryMapper.mapToCategoryEntity(dto);
        CategoryEntity saved = repository.save(entity);
        return CategoryMapper.mapToCategoryDto(saved);
    }

    private CategoryDto updateInternal(Long id, CategoryDto dto) {
        CategoryEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Category not found with id: " + id));

        CategoryEntity source = CategoryMapper.mapToCategoryEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        CategoryEntity saved = repository.save(existing);
        return CategoryMapper.mapToCategoryDto(saved);
    }

    private CategoryDto findByIdInternal(Long id) {
        CategoryEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Category not found with id: " + id));
        return CategoryMapper.mapToCategoryDto(entity);
    }

    private List<CategoryDto> getAllInternal() {
        return repository.findAll().stream().map(CategoryMapper::mapToCategoryDto).toList();
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