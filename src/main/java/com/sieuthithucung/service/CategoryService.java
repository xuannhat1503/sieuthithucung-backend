package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.CategoryDto;
import com.sieuthithucung.entity.CategoryEntity;
import com.sieuthithucung.mapper.CategoryMapper;
import com.sieuthithucung.repository.CategoryRepository;
import org.springframework.stereotype.Service;

@Service
public class CategoryService extends AbstractCrudService<CategoryEntity, Long, CategoryDto> {

    public CategoryService(CategoryRepository repository, CategoryMapper mapper) {
        super(repository, mapper, "Category");
    }
}

