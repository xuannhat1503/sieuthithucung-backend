package com.sieuthithucung.controller;

import com.sieuthithucung.common.AbstractCrudController;
import com.sieuthithucung.dto.NotificationDto;
import com.sieuthithucung.service.NotificationService;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/notifications")
public class NotificationController extends AbstractCrudController<Long, NotificationDto> {

    public NotificationController(NotificationService service) {
        super(service);
    }
}

