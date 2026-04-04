package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.OrderItemDto;
import com.sieuthithucung.entity.OrderItemEntity;

public class OrderItemMapper {
    public static OrderItemDto mapToOrderItemDto(OrderItemEntity entity) {
        return new OrderItemDto(
                entity.getId(),
                entity.getOrderId(),
                entity.getProductId(),
                entity.getUserId(),
                entity.getQuantity(),
                entity.getPrice(),
                entity.getCreatedAt(),
                entity.getUpdatedAt()
        );
    }

    public static OrderItemEntity mapToOrderItemEntity(OrderItemDto dto) {
        return new OrderItemEntity(
                dto.getId(),
                dto.getOrderId(),
                dto.getProductId(),
                dto.getUserId(),
                dto.getQuantity(),
                dto.getPrice(),
                dto.getCreatedAt(),
                dto.getUpdatedAt()
        );
    }
}