package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.NotificationDto;
import com.sieuthithucung.entity.NotificationEntity;
import org.springframework.stereotype.Component;
import tools.jackson.databind.ObjectMapper;

@Component
public class NotificationMapper extends BaseMapper<NotificationEntity, NotificationDto> {

    public NotificationMapper(ObjectMapper objectMapper) {
        super(objectMapper, NotificationEntity.class, NotificationDto.class);
    }
}

