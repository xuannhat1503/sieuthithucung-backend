package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.NotificationDto;
import com.sieuthithucung.entity.NotificationEntity;

public class NotificationMapper {
    public static NotificationDto mapToNotificationDto(NotificationEntity entity) {
        return new NotificationDto(
                entity.getId(),
                entity.getUserId(),
                entity.getType(),
                entity.getMessage(),
                entity.getLink(),
                entity.getIsRead(),
                entity.getCreatedAt(),
                entity.getUpdatedAt()
        );
    }

    public static NotificationEntity mapToNotificationEntity(NotificationDto dto) {
        return new NotificationEntity(
                dto.getId(),
                dto.getUserId(),
                dto.getType(),
                dto.getMessage(),
                dto.getLink(),
                dto.getIsRead(),
                dto.getCreatedAt(),
                dto.getUpdatedAt()
        );
    }
}