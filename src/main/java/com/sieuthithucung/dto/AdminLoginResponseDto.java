package com.sieuthithucung.dto;

import lombok.Builder;
import lombok.Data;

@Data
@Builder
public class AdminLoginResponseDto {
    private String token;
    private Long userId;
    private String name;
    private String email;
    private String role;
}
