package com.sieuthithucung.controller;

import com.sieuthithucung.common.AbstractCrudController;
import com.sieuthithucung.dto.PermissionDto;
import com.sieuthithucung.service.PermissionService;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/permissions")
public class PermissionController extends AbstractCrudController<Long, PermissionDto> {

    public PermissionController(PermissionService service) {
        super(service);
    }
}

