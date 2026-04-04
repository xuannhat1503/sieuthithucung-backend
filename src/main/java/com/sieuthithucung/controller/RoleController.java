package com.sieuthithucung.controller;

import com.sieuthithucung.common.AbstractCrudController;
import com.sieuthithucung.dto.RoleDto;
import com.sieuthithucung.service.RoleService;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/roles")
public class RoleController extends AbstractCrudController<Long, RoleDto> {

    public RoleController(RoleService service) {
        super(service);
    }
}

