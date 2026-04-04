package com.sieuthithucung.dto;

import lombok.Data;
import lombok.NoArgsConstructor;
import lombok.AllArgsConstructor;

import java.time.LocalDateTime;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class RolePermissionDto {
    private Long id;
    private Long roleId;
    private Long permissionId;
    private LocalDateTime createdAt;
    private LocalDateTime updatedAt;
}

