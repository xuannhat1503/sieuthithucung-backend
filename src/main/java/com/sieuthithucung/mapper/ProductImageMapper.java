package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.ProductImageDto;
import com.sieuthithucung.entity.ProductImageEntity;

public class ProductImageMapper {
    public static ProductImageDto mapToProductImageDto(ProductImageEntity entity) {
        return new ProductImageDto(
                entity.getId(),
                entity.getProductId(),
                entity.getImage(),
                entity.getCreatedAt(),
                entity.getUpdatedAt()
        );
    }

    public static ProductImageEntity mapToProductImageEntity(ProductImageDto dto) {
        return new ProductImageEntity(
                dto.getId(),
                dto.getProductId(),
                dto.getImage(),
                dto.getCreatedAt(),
                dto.getUpdatedAt()
        );
    }
}