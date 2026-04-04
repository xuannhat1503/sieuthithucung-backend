package com.sieuthithucung.dto;

import lombok.Data;

import java.time.LocalDateTime;

@Data
public class RolePermissionDto {
    private Long id;
    private Long roleId;
    private Long permissionId;
    private LocalDateTime createdAt;
    private LocalDateTime updatedAt;
}

