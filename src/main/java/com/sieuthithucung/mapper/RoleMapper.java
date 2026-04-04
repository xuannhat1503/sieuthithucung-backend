package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.RoleDto;
import com.sieuthithucung.entity.RoleEntity;

public class RoleMapper {
    public static RoleDto mapToRoleDto(RoleEntity entity) {
        return new RoleDto(
                entity.getId(),
                entity.getName(),
                entity.getCreatedAt(),
                entity.getUpdatedAt()
        );
    }

    public static RoleEntity mapToRoleEntity(RoleDto dto) {
        return new RoleEntity(
                dto.getId(),
                dto.getName(),
                dto.getCreatedAt(),
                dto.getUpdatedAt()
        );
    }
}