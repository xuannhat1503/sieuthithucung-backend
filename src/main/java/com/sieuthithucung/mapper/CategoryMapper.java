package com.sieuthithucung.mapper;

import tools.jackson.databind.ObjectMapper;
import com.sieuthithucung.dto.CategoryDto;
import com.sieuthithucung.entity.CategoryEntity;
import org.springframework.stereotype.Component;

@Component
public class CategoryMapper extends BaseMapper<CategoryEntity, CategoryDto> {

    public CategoryMapper(ObjectMapper objectMapper) {
        super(objectMapper, CategoryEntity.class, CategoryDto.class);
    }
}

