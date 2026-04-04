package com.sieuthithucung.controller;

import com.sieuthithucung.common.AbstractCrudController;
import com.sieuthithucung.dto.UserDto;
import com.sieuthithucung.service.UserService;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/users")
public class UserController extends AbstractCrudController<Long, UserDto> {

    public UserController(UserService service) {
        super(service);
    }
}

