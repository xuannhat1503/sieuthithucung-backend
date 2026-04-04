package com.sieuthithucung.dto;

import lombok.Data;

import java.time.LocalDateTime;

@Data
public class UserDto {
    private Long id;
    private String name;
    private String email;
    private String password;
    private String status;
    private String phoneNumber;
    private String avatar;
    private String address;
    private Long roleId;
    private String activationToken;
    private String googleId;
    private LocalDateTime createdAt;
    private LocalDateTime updatedAt;
}

