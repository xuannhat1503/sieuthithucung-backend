package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.CategoryDto;
import com.sieuthithucung.entity.CategoryEntity;

public class CategoryMapper {
    public static CategoryDto mapToCategoryDto(CategoryEntity entity) {
        return new CategoryDto(
                entity.getId(),
                entity.getName(),
                entity.getSlug(),
                entity.getDescription(),
                entity.getImage(),
                entity.getCreatedAt(),
                entity.getUpdatedAt()
        );
    }

    public static CategoryEntity mapToCategoryEntity(CategoryDto dto) {
        return new CategoryEntity(
                dto.getId(),
                dto.getName(),
                dto.getSlug(),
                dto.getDescription(),
                dto.getImage(),
                dto.getCreatedAt(),
                dto.getUpdatedAt()
        );
    }
}