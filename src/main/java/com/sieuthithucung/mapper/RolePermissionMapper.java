package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.RolePermissionDto;
import com.sieuthithucung.entity.RolePermissionEntity;

public class RolePermissionMapper {
    public static RolePermissionDto mapToRolePermissionDto(RolePermissionEntity entity) {
        return new RolePermissionDto(
                entity.getId(),
                entity.getRoleId(),
                entity.getPermissionId(),
                entity.getCreatedAt(),
                entity.getUpdatedAt()
        );
    }

    public static RolePermissionEntity mapToRolePermissionEntity(RolePermissionDto dto) {
        return new RolePermissionEntity(
                dto.getId(),
                dto.getRoleId(),
                dto.getPermissionId(),
                dto.getCreatedAt(),
                dto.getUpdatedAt()
        );
    }
}