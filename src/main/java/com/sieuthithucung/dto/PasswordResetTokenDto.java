package com.sieuthithucung.dto;

import lombok.Data;

import java.time.LocalDateTime;

@Data
public class PasswordResetTokenDto {
    private String email;
    private String token;
    private LocalDateTime createdAt;
}

