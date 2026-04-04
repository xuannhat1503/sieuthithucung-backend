package com.sieuthithucung.controller;

import com.sieuthithucung.common.AbstractCrudController;
import com.sieuthithucung.dto.RolePermissionDto;
import com.sieuthithucung.service.RolePermissionService;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/role-permissions")
public class RolePermissionController extends AbstractCrudController<Long, RolePermissionDto> {

    public RolePermissionController(RolePermissionService service) {
        super(service);
    }
}

