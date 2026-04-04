package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.CartItemDto;
import com.sieuthithucung.entity.CartItemEntity;

public class CartItemMapper {
    public static CartItemDto mapToCartItemDto(CartItemEntity entity) {
        return new CartItemDto(
                entity.getId(),
                entity.getUserId(),
                entity.getProductId(),
                entity.getQuantity(),
                entity.getCreatedAt(),
                entity.getUpdatedAt()
        );
    }

    public static CartItemEntity mapToCartItemEntity(CartItemDto dto) {
        return new CartItemEntity(
                dto.getId(),
                dto.getUserId(),
                dto.getProductId(),
                dto.getQuantity(),
                dto.getCreatedAt(),
                dto.getUpdatedAt()
        );
    }
}