package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.OrderStatusHistoryDto;
import com.sieuthithucung.entity.OrderStatusHistoryEntity;

public class OrderStatusHistoryMapper {
    public static OrderStatusHistoryDto mapToOrderStatusHistoryDto(OrderStatusHistoryEntity entity) {
        return new OrderStatusHistoryDto(
                entity.getId(),
                entity.getOrderId(),
                entity.getStatus(),
                entity.getNote(),
                entity.getChangedAt(),
                entity.getCreatedAt(),
                entity.getUpdatedAt()
        );
    }

    public static OrderStatusHistoryEntity mapToOrderStatusHistoryEntity(OrderStatusHistoryDto dto) {
        return new OrderStatusHistoryEntity(
                dto.getId(),
                dto.getOrderId(),
                dto.getStatus(),
                dto.getNote(),
                dto.getChangedAt(),
                dto.getCreatedAt(),
                dto.getUpdatedAt()
        );
    }
}