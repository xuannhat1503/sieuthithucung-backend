package com.sieuthithucung.service;

import com.sieuthithucung.dto.AdminLoginRequestDto;
import com.sieuthithucung.dto.AdminLoginResponseDto;
import com.sieuthithucung.entity.RoleEntity;
import com.sieuthithucung.entity.UserEntity;
import com.sieuthithucung.repository.RoleRepository;
import com.sieuthithucung.repository.UserRepository;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;
import org.springframework.web.server.ResponseStatusException;

import java.time.Duration;
import java.time.LocalDateTime;
import java.util.Map;
import java.util.UUID;
import java.util.concurrent.ConcurrentHashMap;

@Service
public class AdminAuthService {

    private static final Duration TOKEN_TTL = Duration.ofHours(12);

    private final UserRepository userRepository;
    private final RoleRepository roleRepository;
    private final Map<String, AdminSession> sessions = new ConcurrentHashMap<>();

    public AdminAuthService(UserRepository userRepository, RoleRepository roleRepository) {
        this.userRepository = userRepository;
        this.roleRepository = roleRepository;
    }

    public AdminLoginResponseDto login(AdminLoginRequestDto request) {
        if (request == null || isBlank(request.getEmail()) || isBlank(request.getPassword())) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "Email va password la bat buoc");
        }

        UserEntity user = userRepository.findByEmail(request.getEmail().trim())
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.UNAUTHORIZED, "Sai thong tin dang nhap"));

        if (!request.getPassword().equals(user.getPassword())) {
            throw new ResponseStatusException(HttpStatus.UNAUTHORIZED, "Sai thong tin dang nhap");
        }

        RoleEntity role = roleRepository.findById(user.getRoleId())
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.FORBIDDEN, "Tai khoan khong co role hop le"));

        if (!isAdminRole(role.getName())) {
            throw new ResponseStatusException(HttpStatus.FORBIDDEN, "Chi tai khoan ADMIN moi duoc vao trang quan tri");
        }

        String token = UUID.randomUUID().toString();
        sessions.put(token, new AdminSession(user.getId(), user.getName(), user.getEmail(), role.getName(), LocalDateTime.now()));

        return AdminLoginResponseDto.builder()
                .token(token)
                .userId(user.getId())
                .name(user.getName())
                .email(user.getEmail())
                .role(role.getName())
                .build();
    }

    public boolean isValidAdminToken(String token) {
        if (isBlank(token)) {
            return false;
        }

        AdminSession session = sessions.get(token);
        if (session == null) {
            return false;
        }

        if (session.createdAt().plus(TOKEN_TTL).isBefore(LocalDateTime.now())) {
            sessions.remove(token);
            return false;
        }

        return isAdminRole(session.roleName());
    }

    public AdminLoginResponseDto getCurrentAdmin(String token) {
        AdminSession session = sessions.get(token);
        if (session == null || !isValidAdminToken(token)) {
            throw new ResponseStatusException(HttpStatus.UNAUTHORIZED, "Token khong hop le hoac da het han");
        }

        return AdminLoginResponseDto.builder()
                .token(token)
                .userId(session.userId())
                .name(session.name())
                .email(session.email())
                .role(session.roleName())
                .build();
    }

    public void logout(String token) {
        if (!isBlank(token)) {
            sessions.remove(token);
        }
    }

    public String extractToken(String headerValue) {
        if (isBlank(headerValue)) {
            return null;
        }

        String value = headerValue.trim();
        if (value.toLowerCase().startsWith("bearer ")) {
            return value.substring(7).trim();
        }
        return value;
    }

    private boolean isAdminRole(String roleName) {
        return roleName != null && "ADMIN".equalsIgnoreCase(roleName.trim());
    }

    private boolean isBlank(String value) {
        return value == null || value.trim().isEmpty();
    }

    private record AdminSession(Long userId, String name, String email, String roleName, LocalDateTime createdAt) {
    }
}
