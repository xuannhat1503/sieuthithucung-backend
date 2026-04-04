package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.ProductDto;
import com.sieuthithucung.entity.ProductEntity;

public class ProductMapper {
    public static ProductDto mapToProductDto(ProductEntity entity) {
        return new ProductDto(
                entity.getId(),
                entity.getName(),
                entity.getSlug(),
                entity.getCategoryId(),
                entity.getDescription(),
                entity.getPrice(),
                entity.getStock(),
                entity.getStatus(),
                entity.getUnit(),
                entity.getCreatedAt(),
                entity.getUpdatedAt()
        );
    }

    public static ProductEntity mapToProductEntity(ProductDto dto) {
        return new ProductEntity(
                dto.getId(),
                dto.getName(),
                dto.getSlug(),
                dto.getCategoryId(),
                dto.getDescription(),
                dto.getPrice(),
                dto.getStock(),
                dto.getStatus(),
                dto.getUnit(),
                dto.getCreatedAt(),
                dto.getUpdatedAt()
        );
    }
}