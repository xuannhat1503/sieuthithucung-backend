package com.sieuthithucung.controller;

import com.sieuthithucung.dto.AdminLoginRequestDto;
import com.sieuthithucung.dto.AdminLoginResponseDto;
import com.sieuthithucung.service.AdminAuthService;
import jakarta.servlet.http.HttpServletRequest;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/v1/auth")
public class AdminAuthController {

    private final AdminAuthService adminAuthService;

    public AdminAuthController(AdminAuthService adminAuthService) {
        this.adminAuthService = adminAuthService;
    }

    @PostMapping("/login")
    public ResponseEntity<AdminLoginResponseDto> login(@RequestBody AdminLoginRequestDto request) {
        return ResponseEntity.ok(adminAuthService.login(request));
    }

    @GetMapping("/me")
    public ResponseEntity<AdminLoginResponseDto> me(HttpServletRequest request) {
        String token = adminAuthService.extractToken(request.getHeader("Authorization"));
        return ResponseEntity.ok(adminAuthService.getCurrentAdmin(token));
    }

    @PostMapping("/logout")
    public ResponseEntity<Void> logout(HttpServletRequest request) {
        String token = adminAuthService.extractToken(request.getHeader("Authorization"));
        adminAuthService.logout(token);
        return ResponseEntity.noContent().build();
    }
}
