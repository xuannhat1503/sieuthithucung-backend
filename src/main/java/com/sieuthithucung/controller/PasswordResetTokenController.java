package com.sieuthithucung.controller;

import com.sieuthithucung.common.AbstractCrudController;
import com.sieuthithucung.dto.PasswordResetTokenDto;
import com.sieuthithucung.service.PasswordResetTokenService;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/password-reset-tokens")
public class PasswordResetTokenController extends AbstractCrudController<String, PasswordResetTokenDto> {

    public PasswordResetTokenController(PasswordResetTokenService service) {
        super(service);
    }
}

