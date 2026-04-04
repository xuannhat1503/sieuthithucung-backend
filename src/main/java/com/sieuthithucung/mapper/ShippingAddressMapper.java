package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.ShippingAddressDto;
import com.sieuthithucung.entity.ShippingAddressEntity;

public class ShippingAddressMapper {
    public static ShippingAddressDto mapToShippingAddressDto(ShippingAddressEntity entity) {
        return new ShippingAddressDto(
                entity.getId(),
                entity.getUserId(),
                entity.getFullName(),
                entity.getPhone(),
                entity.getAddress(),
                entity.getCity(),
                entity.getDefaultAddress(),
                entity.getCreatedAt(),
                entity.getUpdatedAt()
        );
    }

    public static ShippingAddressEntity mapToShippingAddressEntity(ShippingAddressDto dto) {
        return new ShippingAddressEntity(
                dto.getId(),
                dto.getUserId(),
                dto.getFullName(),
                dto.getPhone(),
                dto.getAddress(),
                dto.getCity(),
                dto.getDefaultAddress(),
                dto.getCreatedAt(),
                dto.getUpdatedAt()
        );
    }
}