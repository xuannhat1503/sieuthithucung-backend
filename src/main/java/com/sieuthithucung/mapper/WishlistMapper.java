package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.WishlistDto;
import com.sieuthithucung.entity.WishlistEntity;

public class WishlistMapper {
    public static WishlistDto mapToWishlistDto(WishlistEntity entity) {
        return new WishlistDto(
                entity.getId(),
                entity.getUserId(),
                entity.getProductId(),
                entity.getCreatedAt(),
                entity.getUpdatedAt()
        );
    }

    public static WishlistEntity mapToWishlistEntity(WishlistDto dto) {
        return new WishlistEntity(
                dto.getId(),
                dto.getUserId(),
                dto.getProductId(),
                dto.getCreatedAt(),
                dto.getUpdatedAt()
        );
    }
}