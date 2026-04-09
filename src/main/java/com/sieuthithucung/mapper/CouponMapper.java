package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.CouponDto;
import com.sieuthithucung.entity.CouponEntity;

public class CouponMapper {
    public static CouponDto mapToCouponDto(CouponEntity entity) {
        return new CouponDto(
                entity.getId(),
                entity.getCode(),
                entity.getType(),
                entity.getDiscount(),
                entity.getMinSubtotal(),
                entity.getMaxDiscount(),
                entity.getLabel(),
                entity.getExpiredAt(),
                entity.getIsActive(),
                entity.getCreatedAt(),
                entity.getUpdatedAt()
        );
    }

    public static CouponEntity mapToCouponEntity(CouponDto dto) {
        return new CouponEntity(
                dto.getId(),
                dto.getCode(),
                dto.getType(),
                dto.getDiscount(),
                dto.getMinSubtotal(),
                dto.getMaxDiscount(),
                dto.getLabel(),
                dto.getExpiredAt(),
                dto.getIsActive(),
                dto.getCreatedAt(),
                dto.getUpdatedAt()
        );
    }
}
