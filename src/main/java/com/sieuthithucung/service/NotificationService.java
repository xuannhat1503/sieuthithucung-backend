package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.NotificationDto;
import com.sieuthithucung.entity.NotificationEntity;
import com.sieuthithucung.mapper.NotificationMapper;
import com.sieuthithucung.repository.NotificationRepository;
import org.springframework.stereotype.Service;

@Service
public class NotificationService extends AbstractCrudService<NotificationEntity, Long, NotificationDto> {

    public NotificationService(NotificationRepository repository, NotificationMapper mapper) {
        super(repository, mapper, "Notification");
    }
}

