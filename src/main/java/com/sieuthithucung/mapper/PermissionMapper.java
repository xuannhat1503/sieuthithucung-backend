package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.PermissionDto;
import com.sieuthithucung.entity.PermissionEntity;

public class PermissionMapper {
    public static PermissionDto mapToPermissionDto(PermissionEntity entity) {
        return new PermissionDto(
                entity.getId(),
                entity.getName(),
                entity.getCreatedAt(),
                entity.getUpdatedAt()
        );
    }

    public static PermissionEntity mapToPermissionEntity(PermissionDto dto) {
        return new PermissionEntity(
                dto.getId(),
                dto.getName(),
                dto.getCreatedAt(),
                dto.getUpdatedAt()
        );
    }
}