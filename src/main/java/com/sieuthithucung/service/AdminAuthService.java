package com.sieuthithucung.service;

import com.sieuthithucung.dto.AdminLoginRequestDto;
import com.sieuthithucung.dto.AdminLoginResponseDto;

public interface AdminAuthService {

    AdminLoginResponseDto login(AdminLoginRequestDto request);

    boolean isValidAdminToken(String token);

    AdminLoginResponseDto getCurrentAdmin(String token);

    void logout(String token);

    String extractToken(String headerValue);
}
