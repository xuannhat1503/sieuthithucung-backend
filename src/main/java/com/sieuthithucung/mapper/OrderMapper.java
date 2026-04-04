package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.OrderDto;
import com.sieuthithucung.entity.OrderEntity;

public class OrderMapper {
    public static OrderDto mapToOrderDto(OrderEntity entity) {
        return new OrderDto(
                entity.getId(),
                entity.getUserId(),
                entity.getTotalPrice(),
                entity.getStatus(),
                entity.getShippingAddressId(),
                entity.getCreatedAt(),
                entity.getUpdatedAt()
        );
    }

    public static OrderEntity mapToOrderEntity(OrderDto dto) {
        return new OrderEntity(
                dto.getId(),
                dto.getUserId(),
                dto.getTotalPrice(),
                dto.getStatus(),
                dto.getShippingAddressId(),
                dto.getCreatedAt(),
                dto.getUpdatedAt()
        );
    }
}